<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if(session('dev_reset_link'))
        <div class="mb-6 p-5 rounded-xl bg-green-50 border border-green-200 dark:bg-green-900/30 dark:border-green-800 shadow-sm transition-all">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <p class="text-sm font-bold text-green-800 dark:text-green-300 mb-1">
                        {{ __('وضع التطوير السريع: ') }}
                    </p>
                    <p class="text-xs text-green-700 dark:text-green-400 mb-3 leading-relaxed">
                        {{ __('لأن المتجر يعمل على جهازك حالياً، قمنا بتوليد الرابط لك مباشرة بدلاً من إرساله للبريد الإلكتروني لتوفير الوقت:') }}
                    </p>
                    <a href="{{ session('dev_reset_link') }}" class="inline-block px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                        {{ __('اضغط هنا لتغيير كلمة المرور الآن') }} &rarr;
                    </a>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
