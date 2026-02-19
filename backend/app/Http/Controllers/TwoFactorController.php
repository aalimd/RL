<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class TwoFactorController extends Controller
{
    /**
     * Show the 2FA settings page.
     */
    public function index()
    {
        $user = auth()->user();
        $settings = $this->getLayoutSettings();
        if ($user->two_factor_confirmed_at) {
            return view('admin.settings.security', [
                'settings' => $settings,
                'enabled' => true,
                'method' => $user->two_factor_method
            ]);
        }

        return view('admin.settings.security', [
            'settings' => $settings,
            'enabled' => false,
        ]);
    }

    /**
     * Initiate 2FA setup (Generate Secret/QR or Send Email).
     */
    public function enable(Request $request)
    {
        $user = auth()->user();
        $method = $request->input('method', 'app'); // 'app' or 'email'

        if ($method === 'app') {
            $google2fa = app('pragmarx.google2fa');
            $secret = $google2fa->generateSecretKey();

            $request->session()->put('2fa_setup_secret', $secret);
            $request->session()->put('2fa_setup_method', 'app');

            $qrCodeUrl = $google2fa->getQRCodeUrl(
                'RecommendationSystem',
                $user->email,
                $secret
            );

            return response()->json([
                'secret' => $secret,
                'qr_code_url' => $qrCodeUrl
            ]);
        } else {
            // Email Logic
            $code = (string) random_int(100000, 999999);
            $request->session()->put('2fa_setup_code_hash', Hash::make($code));
            $request->session()->put('2fa_setup_expires_at', now()->addMinutes(10)->timestamp);
            $request->session()->put('2fa_setup_method', 'email');

            try {
                Mail::raw("Your 2FA Code is: $code", function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('2FA Verification Code');
                });
            } catch (\Exception $e) {
                return response()->json(['message' => 'Failed to send email. Check SMTP settings.'], 500);
            }

            return response()->json(['message' => 'Code sent to email']);
        }
    }

    /**
     * Confirm 2FA setup with OTP code.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = auth()->user();
        $code = $request->input('code');
        $method = session('2fa_setup_method');

        if (!$method) {
            return response()->json(['message' => 'Invalid session. Refresh.'], 400);
        }

        if ($method === 'app') {
            $secret = session('2fa_setup_secret');
            $google2fa = app('pragmarx.google2fa');

            $valid = $google2fa->verifyKey($secret, $code);

            if ($valid) {
                $user->forceFill([
                    'two_factor_secret' => $secret,
                    'two_factor_method' => 'app',
                    'two_factor_confirmed_at' => now(),
                ])->save();

                session()->forget(['2fa_setup_secret', '2fa_setup_method']);
                session()->put('2fa_verified', true); // Auto verify current session

                return response()->json(['success' => true]);
            }
        } elseif ($method === 'email') {
            $sessionCodeHash = session('2fa_setup_code_hash');
            $setupExpiresAt = (int) session('2fa_setup_expires_at', 0);

            if (!$sessionCodeHash || $setupExpiresAt < now()->timestamp) {
                session()->forget(['2fa_setup_code_hash', '2fa_setup_expires_at', '2fa_setup_method']);
                return response()->json(['message' => 'Verification session expired. Please try setup again.'], 422);
            }

            if (Hash::check($code, $sessionCodeHash)) {
                $user->forceFill([
                    'two_factor_method' => 'email',
                    'two_factor_confirmed_at' => now(),
                ])->save();

                session()->forget(['2fa_setup_code_hash', '2fa_setup_expires_at', '2fa_setup_method']);
                session()->put('2fa_verified', true);

                return response()->json(['success' => true]);
            }
        }

        return response()->json(['message' => 'Invalid code.'], 422);
    }

    /**
     * Disable 2FA.
     */
    public function disable(Request $request)
    {
        $user = auth()->user();
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_method' => null,
            'two_factor_email_code' => null,
            'two_factor_expires_at' => null,
        ])->save();

        $request->session()->forget(['2fa_verified']);

        return back()->with('success', 'Two-Factor Authentication disabled.');
    }

    /**
     * Show the 2FA verification page during login.
     */
    public function verify()
    {
        $user = auth()->user();

        // If not logged in or not 2FA enabled, redirect
        if (!$user || !$user->two_factor_confirmed_at) {
            return redirect('/admin/dashboard');
        }

        // If already verified
        if (session('2fa_verified')) {
            return redirect('/admin/dashboard');
        }

        // Send a code when no valid unexpired code exists.
        if ($user->two_factor_method === 'email') {
            $codeExpired = !$user->two_factor_expires_at || now()->greaterThan($user->two_factor_expires_at);
            if (!$user->two_factor_email_code || $codeExpired) {
                if ($this->issueEmailLoginCode($user)) {
                    session()->flash('success', 'A verification code has been sent to your email.');
                } else {
                    session()->flash('error', 'Failed to send email code.');
                }
            }
        }

        return view('auth.verify-2fa', [
            'method' => $user->two_factor_method,
            'settings' => $this->getLayoutSettings(),
        ]);
    }

    /**
     * Handle verification submission.
     */
    public function verifyPost(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = auth()->user();
        $code = $request->input('code');

        if ($user->two_factor_method === 'app') {
            $google2fa = app('pragmarx.google2fa');
            // The model cast already decrypts this value for us.
            $secret = $user->two_factor_secret;
            if (!$secret) {
                return back()->withErrors(['code' => 'Two-factor configuration is invalid. Please set it up again.']);
            }
            $valid = $google2fa->verifyKey($secret, $code);

            if ($valid) {
                $request->session()->put('2fa_verified', true);
                return redirect('/admin/dashboard');
            }
        } elseif ($user->two_factor_method === 'email') {
            if (!$user->two_factor_email_code || !$user->two_factor_expires_at || now()->greaterThan($user->two_factor_expires_at)) {
                $user->forceFill([
                    'two_factor_email_code' => null,
                    'two_factor_expires_at' => null,
                ])->save();

                return back()->withErrors(['code' => 'Verification code expired. Please request a new code.']);
            }

            if (Hash::check($code, $user->two_factor_email_code)) {
                $request->session()->put('2fa_verified', true);
                $user->forceFill([
                    'two_factor_email_code' => null,
                    'two_factor_expires_at' => null,
                ])->save();

                return redirect('/admin/dashboard');
            }
        }

        return back()->withErrors(['code' => 'Invalid verification code.']);
    }

    /**
     * Resend verification code (Email only).
     */
    public function resend()
    {
        $user = auth()->user();

        if ($user->two_factor_method === 'email') {
            if ($this->issueEmailLoginCode($user)) {
                return back()->with('success', 'Code resent successfully.');
            }

            return back()->with('error', 'Failed to send email.');
        }

        return back();
    }

    private function issueEmailLoginCode($user): bool
    {
        $code = (string) random_int(100000, 999999);
        $user->forceFill([
            'two_factor_email_code' => Hash::make($code),
            'two_factor_expires_at' => now()->addMinutes(10),
        ])->save();

        try {
            Mail::raw("Your Login Code: $code", function ($message) use ($user) {
                $message->to($user->email)->subject('Login Verification Code');
            });

            return true;
        } catch (\Exception $e) {
            $user->forceFill([
                'two_factor_email_code' => null,
                'two_factor_expires_at' => null,
            ])->save();

            return false;
        }
    }

    private function getLayoutSettings(): array
    {
        return Settings::all()->pluck('value', 'key')->toArray();
    }
}
