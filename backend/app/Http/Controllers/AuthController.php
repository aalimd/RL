<?php

namespace App\Http\Controllers;

use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    // POST /api/auth/register
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', \Illuminate\Validation\Rules\Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'viewer', // Default to viewer - admins must upgrade manually
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    // POST /api/auth/login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'two_factor_code' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Account is deactivated. Please contact support.'], 403);
        }

        if ($this->requiresApiTwoFactor($user)) {
            $twoFactorCode = trim((string) $request->input('two_factor_code', ''));

            if ($twoFactorCode === '') {
                if ($user->two_factor_method === 'email') {
                    if (!$this->issueEmailLoginCode($user)) {
                        return response()->json(['message' => 'Failed to send two-factor code. Check email settings.'], 500);
                    }

                    return response()->json([
                        'message' => 'Two-factor code required. A verification code has been sent to your email.',
                        'requires_two_factor' => true,
                        'two_factor_method' => 'email',
                    ], 202);
                }

                return response()->json([
                    'message' => 'Two-factor code required for this account.',
                    'requires_two_factor' => true,
                    'two_factor_method' => $user->two_factor_method,
                ], 202);
            }

            if (!$this->verifyApiTwoFactorCode($user, $twoFactorCode)) {
                return response()->json([
                    'message' => 'Invalid or expired two-factor code.',
                    'requires_two_factor' => true,
                    'two_factor_method' => $user->two_factor_method,
                ], 422);
            }
        }

        $abilities = ['*'];
        if ($this->requiresApiTwoFactor($user)) {
            $abilities[] = 'two-factor-authenticated';
        }

        $token = $user->createToken('auth_token', $abilities)->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    // GET /api/auth/me
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // POST /api/auth/logout
    public function logout(Request $request)
    {
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json(['message' => 'Logged out']);
    }

    private function requiresApiTwoFactor(User $user): bool
    {
        return $user->two_factor_confirmed_at
            && in_array($user->role, ['admin', 'editor'], true);
    }

    private function verifyApiTwoFactorCode(User $user, string $code): bool
    {
        if ($user->two_factor_method === 'app') {
            $secret = $user->two_factor_secret;
            if (!$secret) {
                return false;
            }

            return $this->google2fa()->verifyKey($secret, $code);
        }

        if ($user->two_factor_method === 'email') {
            if (
                !$user->two_factor_email_code
                || !$user->two_factor_expires_at
                || now()->greaterThan($user->two_factor_expires_at)
            ) {
                $user->forceFill([
                    'two_factor_email_code' => null,
                    'two_factor_expires_at' => null,
                ])->save();

                return false;
            }

            $isValid = Hash::check($code, $user->two_factor_email_code);

            if ($isValid) {
                $user->forceFill([
                    'two_factor_email_code' => null,
                    'two_factor_expires_at' => null,
                ])->save();
            }

            return $isValid;
        }

        return false;
    }

    private function issueEmailLoginCode(User $user): bool
    {
        $code = (string) random_int(100000, 999999);
        $user->forceFill([
            'two_factor_email_code' => Hash::make($code),
            'two_factor_expires_at' => now()->addMinutes(10),
        ])->save();

        try {
            Mail::to($user->email)->send(new TwoFactorCodeMail(
                $code,
                $user->name,
                'complete sign-in'
            ));

            return true;
        } catch (\Exception $e) {
            $user->forceFill([
                'two_factor_email_code' => null,
                'two_factor_expires_at' => null,
            ])->save();

            return false;
        }
    }

    private function google2fa(): Google2FA
    {
        return new Google2FA();
    }
}
