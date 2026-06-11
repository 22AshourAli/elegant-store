<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label class="pb-3" for="name" :value="__('global.name')" />
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="{{ __('global.name_placeholder') }}" class="block w-full pe-10 border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300 rounded-lg shadow-sm px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label class="pb-3" for="email" :value="__('global.email')" />
            <input id="email" type="email" name="email" value="{{ request('email', old('email')) }}" required autocomplete="username" placeholder="{{ __('global.email_placeholder') }}" class="block w-full pe-10 border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300 rounded-lg shadow-sm px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label class="pb-3" for="password" :value="__('global.password')" />

            <div x-data="{ show: false }" class="relative mt-1">
                <input id="password" name="password" x-bind:type="show ? 'text' : 'password'" type="password" required autocomplete="new-password" placeholder="{{ __('global.password_placeholder') }}" class="block w-full pe-10 border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300 rounded-lg shadow-sm px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 text-sm" />

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

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label class="pb-3" for="password_confirmation" :value="__('global.confirm_password')" />

            <div x-data="{ show: false }" class="relative mt-1">
                <input id="password_confirmation" name="password_confirmation" x-bind:type="show ? 'text' : 'password'" type="password" required autocomplete="new-password" placeholder="{{ __('global.password_confirmation_placeholder') }}" class="block w-full pe-10 border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300 rounded-lg shadow-sm px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 text-sm" />

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

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Submit Button -->
        <div class="mt-6">
            <x-primary-button class="w-full justify-center py-3 text-sm">
                {{ __('global.sign_up') }}
            </x-primary-button>
        </div>

        <!-- Login Link -->
        <p class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
            {{ __('global.already_registered') }}
            <a class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline" href="{{ route('login') }}">
                {{ __('global.sign_in') }}
            </a>
        </p>
    </form>


</x-guest-layout>
