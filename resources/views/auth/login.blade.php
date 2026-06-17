<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- Email --}}
        <div x-data="{
            value: '{{ request('email', old('email')) }}',
            touched: false,
            get valid() { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value); },
            get error() { return this.touched && this.value.length > 0 && !this.valid ? '{{ __('global.email_invalid') }}' : (this.touched && this.value.length === 0 ? '{{ __('global.field_required') }}' : ''); }
        }" class="group">
            <x-input-label for="email" :value="__('global.email')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 transition-colors duration-200" :class="error ? 'text-red-400' : (valid && value.length > 0 ? 'text-emerald-500' : 'text-slate-400')" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <input id="email" type="email" name="email" x-model="value" @blur="touched = true" required autofocus autocomplete="username"
                       placeholder="you@example.com"
                       class="block w-full ps-10 pe-10 rounded-xl border bg-white/70 dark:bg-gray-900/70 text-sm transition-all duration-200 py-2.5 outline-none"
                       :class="error ? 'border-2 border-red-400' : (valid && value.length > 0 ? 'border-2 border-emerald-400' : 'border border-slate-300 dark:border-slate-600 focus:border-2 focus:border-indigo-500')">
                <div class="absolute inset-y-0 end-0 flex items-center pe-3">
                    <template x-if="value.length > 0 && !error">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                    <template x-if="error">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                </div>
            </div>
            <template x-if="error">
                <p class="mt-1 text-xs font-semibold text-red-500" x-text="error"></p>
            </template>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        {{-- Password --}}
        <div x-data="{
            value: '',
            touched: false,
            show: false,
            get valid() { return this.value.length >= 8; },
            get error() { return this.touched && this.value.length > 0 && !this.valid ? '{{ __('global.password_length') }}' : (this.touched && this.value.length === 0 ? '{{ __('global.field_required') }}' : ''); }
        }" class="group">
            <x-input-label for="password" :value="__('global.password')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 transition-colors duration-200" :class="error ? 'text-red-400' : (valid && value.length > 0 ? 'text-emerald-500' : 'text-slate-400')" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <input id="password" name="password" x-model="value" @blur="touched = true" x-bind:type="show ? 'text' : 'password'" required autocomplete="current-password"
                       placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;"
                       class="block w-full ps-10 pe-12 rounded-xl border bg-white/70 dark:bg-gray-900/70 text-sm transition-all duration-200 py-2.5 outline-none"
                       :class="error ? 'border-2 border-red-400' : (valid && value.length > 0 ? 'border-2 border-emerald-400' : 'border border-slate-300 dark:border-slate-600 focus:border-2 focus:border-indigo-500')">
                <button type="button" @click="show = !show" class="absolute inset-y-0 end-0 flex items-center pe-3.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors focus:outline-none">
                    <template x-if="!show">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </template>
                    <template x-if="show">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.965 9.965 0 012.223-3.57M6.1 6.1A9.966 9.966 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.985 9.985 0 01-4.12 5.2M3 3l18 18"/></svg>
                    </template>
                </button>
            </div>
            {{-- Strength indicator --}}
            <div x-show="value.length > 0" x-transition class="mt-2">
                <div class="flex gap-1">
                    <template x-for="i in 4" :key="i">
                        <div class="h-1 flex-1 rounded-full transition-all duration-300"
                             :class="value.length >= i * 2 ? (
                                i <= 2 ? (value.length >= 8 ? 'bg-emerald-400' : 'bg-amber-400') :
                                i <= 3 ? (value.length >= 10 ? 'bg-emerald-400' : 'bg-slate-200 dark:bg-slate-700') :
                                (value.length >= 12 ? 'bg-emerald-400' : 'bg-slate-200 dark:bg-slate-700')
                             ) : 'bg-slate-200 dark:bg-slate-700'">
                        </div>
                    </template>
                </div>
                <p class="mt-1 text-[10px] font-semibold"
                   :class="value.length >= 8 ? 'text-emerald-500' : 'text-amber-500'"
                   x-text="value.length >= 12 ? '{{ __('global.password_strong') }}' : (value.length >= 8 ? '{{ __('global.password_ok') }}' : '{{ __('global.password_weak') }}')">
                </p>
            </div>
            <template x-if="error">
                <p class="mt-1 text-xs font-semibold text-red-500" x-text="error"></p>
            </template>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        {{-- Remember & Forgot --}}
        <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 cursor-pointer group">
                <input id="remember_me" type="checkbox" name="remember"
                       class="w-4 h-4 rounded-md border-2 border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 transition cursor-pointer">
                <span class="text-xs font-semibold text-slate-600 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200 transition">{{ __('global.remember_me') }}</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 hover:underline transition" href="{{ route('password.request') }}">
                    {{ __('global.forgot_password') }}
                </a>
            @endif
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-extrabold py-3 text-sm shadow-lg shadow-indigo-200 dark:shadow-indigo-900/30 hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
            <span class="flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                {{ __('global.sign_in') }}
            </span>
        </button>

        {{-- Register CTA --}}
        <p class="text-center text-xs font-semibold text-slate-500 dark:text-slate-400">
            {{ __('global.no_account') }}
            <a href="{{ route('register') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 hover:underline font-bold transition">
                {{ __('global.create_account') }}
            </a>
        </p>
    </form>
</x-guest-layout>