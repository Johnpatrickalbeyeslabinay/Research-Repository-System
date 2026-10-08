<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (! session()->has('login_captcha')) {
            $this->generateCaptcha();
        }

        return view('login', [
            'captchaCode' => session('login_captcha'),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
            'captcha' => 'required',
        ]);

        // CAPTCHA validation
        $captchaInput = strtoupper(trim($request->captcha));
        $captchaCode = session('login_captcha');

        if (
            ! $captchaCode ||
            ! hash_equals($captchaCode, $captchaInput)
        ) {
            $this->generateCaptcha();

            return back()
                ->withErrors([
                    'captcha' => 'Incorrect CAPTCHA code. Please try again.',
                ])
                ->withInput();
        }

        // CAPTCHA is valid — remove it so it cannot be reused
        session()->forget('login_captcha');

        $login = $request->login;

        // Check kung email o User ID ang inilagay
        $field = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'user_id';

        if (
            Auth::attempt([
                $field => $login,
                'password' => $request->password,
            ])
        ) {
            $request->session()->regenerate();

            $user = Auth::user();

            /*
            |--------------------------------------------------------------------------
            | Email Verification
            |--------------------------------------------------------------------------
            */

            if (! $user->email_verified_at) {
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $this->generateCaptcha();

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'login' => 'Please verify your email address before signing in.',
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Role Redirect
            |--------------------------------------------------------------------------
            */

            // Show welcome modal once after successful login
            $request->session()->flash('show_welcome_modal', true);

            if ($user->role == 'student') {
                return redirect()->route('student');
            }

            if ($user->role == 'instructor') {
                return redirect()->route('instructor');
            }

            if ($user->role == 'admin') {
                return redirect()->route('admin');
            }
        }

        // Wrong credentials
        $this->generateCaptcha();

        return back()
            ->withErrors([
                'login' => 'Invalid User ID or Password.',
            ])
            ->withInput();
    }

    /**
     * Generate a random 6-character CAPTCHA.
     */
    private function generateCaptcha()
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        $code = '';

        for ($i = 0; $i < 6; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        session(['login_captcha' => $code]);
    }

    public function verifyEmail(Request $request, $user, $hash)
    {
        $user = User::findOrFail($user);

        abort_unless(
            hash_equals(
                sha1($user->getEmailForVerification()),
                $hash
            ),
            403
        );

        if ($user->email_verified_at) {
            return redirect()->route('login')->with(
                'success',
                'Your email address is already verified.'
            );
        }

        $user->email_verified_at = now();
        $user->save();

        return redirect()->route('login')->with(
            'success',
            'Your email address has been verified. You can now sign in.'
        );
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
