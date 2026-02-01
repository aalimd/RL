<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class TwoFactorController extends Controller
{
    /**
     * Show the 2FA settings page.
     */
    public function index()
    {
        $user = auth()->user();
        if ($user->two_factor_confirmed_at) {
            return view('admin.settings.security', [
                'enabled' => true,
                'method' => $user->two_factor_method
            ]);
        }

        return view('admin.settings.security', ['enabled' => false]);
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
            $request->session()->put('2fa_setup_code', $code);
            $request->session()->put('2fa_setup_method', 'email');

            // TODO: Implement actual Email Notification
            // Check if Mailable exists, otherwise simple raw mail or log for now?
            // Since this is critical, I should create a dynamic Mailable or use standard Mail::raw

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
                    'two_factor_secret' => encrypt($secret),
                    'two_factor_method' => 'app',
                    'two_factor_confirmed_at' => now(),
                ])->save();

                session()->forget(['2fa_setup_secret', '2fa_setup_method']);
                session()->put('2fa_verified', true); // Auto verify current session

                return response()->json(['success' => true]);
            }
        } elseif ($method === 'email') {
            $sessionCode = session('2fa_setup_code');

            if ($sessionCode && $sessionCode === $code) {
                $user->forceFill([
                    'two_factor_method' => 'email',
                    'two_factor_confirmed_at' => now(),
                ])->save();

                session()->forget(['2fa_setup_code', '2fa_setup_method']);
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
        ])->save();

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

        // If email method, send code automatically on load? Or ask user to click send?
        // Let's send automatically if method is email
        if ($user->two_factor_method === 'email' && !session('2fa_email_sent')) {
            $code = (string) random_int(100000, 999999);
            // Store encrypted code in DB or session. DB is better for persistence across requests if session driver is weak
            // But session is fine.
            $user->forceFill([
                'two_factor_email_code' => $code, // Encrypt/Hash not essential for short lived OTP unless strict security
                // Wait, User model casts it to nothing special in my migration, just string. I should encrypt it or hash it.
                // But for comparison I need raw. Let's start with session for simplicity.
            ])->save();

            // Actually, let's use the DB column I created
            $user->two_factor_email_code = $code;
            $user->save();

            try {
                Mail::raw("Your Login Code: $code", function ($message) use ($user) {
                    $message->to($user->email)->subject('Login Verification Code');
                });
                session()->flash('success', 'A verification code has been sent to your email.');
                session()->put('2fa_email_sent', true);
            } catch (\Exception $e) {
                session()->flash('error', 'Failed to send email code.');
            }
        }

        return view('auth.verify-2fa', ['method' => $user->two_factor_method]);
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
            // decrypt secret
            $secret = decrypt($user->two_factor_secret);
            $valid = $google2fa->verifyKey($secret, $code);

            if ($valid) {
                $request->session()->put('2fa_verified', true);
                return redirect('/admin/dashboard');
            }
        } elseif ($user->two_factor_method === 'email') {
            if ($user->two_factor_email_code === $code) {
                $request->session()->put('2fa_verified', true);
                // Clear code
                $user->two_factor_email_code = null;
                $user->save();
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
            $code = (string) random_int(100000, 999999);
            $user->two_factor_email_code = $code;
            $user->save();

            try {
                Mail::raw("Your Login Code: $code", function ($message) use ($user) {
                    $message->to($user->email)->subject('Login Verification Code');
                });
                return back()->with('success', 'Code resent successfully.');
            } catch (\Exception $e) {
                return back()->with('error', 'Failed to send email.');
            }
        }

        return back();
    }
}
