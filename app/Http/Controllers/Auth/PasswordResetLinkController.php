<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportException;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $status = Password::sendResetLink($request->only('email'));

            if ($status == Password::RESET_LINK_SENT && config('app.env') === 'local') {
                $user = \App\Models\User::where('email', $request->email)->first();
                if ($user) {
                    $token = Password::broker()->createToken($user);
                    $link = route('password.reset', ['token' => $token, 'email' => $request->email]);
                    return back()->with('status', __($status))->with('dev_reset_link', $link);
                }
            }

            if ($status == Password::RESET_LINK_SENT) {
                return back()->with('status', __($status));
            }

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
        } catch (TransportException $e) {
            return back()->with('status', __('passwords.sent'));
        } catch (\Exception $e) {
            return back()->with('status', __('passwords.sent'));
        }
    }
}
