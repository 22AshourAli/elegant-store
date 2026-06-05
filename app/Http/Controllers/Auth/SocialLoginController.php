<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialLoginController extends Controller
{
    public function redirectToProvider(string $provider)
    {
        if (!in_array($provider, ['google', 'github', 'microsoft'])) {
            abort(404, 'Provider not supported');
        }

        $clientId = config("services.{$provider}.client_id");
        $clientSecret = config("services.{$provider}.client_secret");

        if (empty($clientId) || empty($clientSecret) || str_contains($clientId, 'أدخل_معرف') || str_contains($clientId, 'placeholder') || $clientId === 'GOOGLE_CLIENT_ID') {
            return redirect()->route('login')->with('error', "يرجى تهيئة معرفات الدخول الاجتماعي (" . ucfirst($provider) . ") في ملف الإعدادات .env لتفعيل هذه الميزة.");
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from the provider.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback(string $provider)
    {
        if (!in_array($provider, ['google', 'github', 'microsoft'])) {
            abort(404, 'Provider not supported');
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'حدث خطأ أثناء تسجيل الدخول عبر ' . ucfirst($provider) . ': ' . $e->getMessage());
        }

        $email = $socialUser->getEmail();
        if (!$email) {
            return redirect()->route('login')->with('error', 'فشل الحصول على البريد الإلكتروني من حساب ' . ucfirst($provider));
        }

        // Find or create user
        $user = User::where('email', $email)->first();

        if ($user) {
            // Update social info if not already set
            $user->update([
                'social_id' => $socialUser->getId(),
                'social_type' => $provider,
            ]);
        } else {
            // Create a new user
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? ucfirst($provider) . ' User',
                'email' => $email,
                'password' => bcrypt(Str::random(24)), // Random secure password since they log in via social
                'role' => 'customer',
                'social_id' => $socialUser->getId(),
                'social_type' => $provider,
            ]);
        }

        Auth::login($user, true);

        // Redirect to intended URL (like Checkout) or dashboard
        return redirect()->intended(route('dashboard'));
    }
}
