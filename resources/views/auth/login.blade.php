<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label class="pb-3" for="email" :value="__('global.email')" />
            <input id="email" type="email" name="email" value="{{ request('email', old('email')) }}" required autofocus autocomplete="username" placeholder="{{ __('global.email_placeholder') }}" class="block w-full pe-10 border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300 rounded-lg shadow-sm px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label class="pb-3" for="password" :value="__('global.password')" />

            <div x-data="{ show: false }" class="relative mt-1">
                <input id="password" name="password" x-bind:type="show ? 'text' : 'password'" type="password" required autocomplete="current-password" placeholder="{{ __('global.password_placeholder') }}" class="block w-full pe-10 border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300 rounded-lg shadow-sm px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 text-sm" />

                <button type="button" @click="show = !show" class="absolute inset-y-0 end-0 flex items-center pe-3 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none" :aria-label="show ? 'Hide password' : 'Show password'">
                    <template x-if="!show">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </template>
                    <template x-if="show">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.965 9.965 0 012.223-3.57M6.1 6.1A9.966 9.966 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.985 9.985 0 01-4.12 5.2M3 3l18 18" />
                        </svg>
                    </template>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-950 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-900" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('global.remember_me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline font-medium" href="{{ route('password.request') }}">
                    {{ __('global.forgot_password') }}
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="mt-6">
            <x-primary-button class="w-full justify-center py-3 text-sm">
                {{ __('global.sign_in') }}
            </x-primary-button>
        </div>

        <!-- Register Link -->
        <p class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
            {{ __('global.no_account') }}
            <a class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline" href="{{ route('register') }}">
                {{ __('global.create_account') }}
            </a>
        </p>
    </form>


</x-guest-layout>
