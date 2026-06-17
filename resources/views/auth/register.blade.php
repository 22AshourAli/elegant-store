<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        {{-- Name --}}
        <div x-data="{
            value: '{{ old('name') }}',
            touched: false,
            get valid() { return this.value.trim().length >= 2; },
            get error() { return this.touched && this.value.length > 0 && !this.valid ? '{{ __('global.name_min_length') }}' : (this.touched && this.value.length === 0 ? '{{ __('global.field_required') }}' : ''); }
        }" class="group">
            <x-input-label for="name" :value="__('global.name')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 transition-colors duration-200" :class="error ? 'text-red-400' : (valid && value.length > 0 ? 'text-emerald-500' : 'text-slate-400')" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <input id="name" type="text" name="name" x-model="value" @blur="touched = true" required autofocus autocomplete="name"
                       placeholder="{{ __('global.name_placeholder') }}"
                       class="block w-full ps-10 pe-10 rounded-xl border bg-white/70 dark:bg-gray-900/70 text-sm transition-all duration-300 py-2.5 outline-none focus:outline-none focus:ring-4 shadow-sm"
                       :class="error ? 'border-red-450 focus:border-red-500 focus:ring-red-500/10' : (valid && value.length > 0 ? 'border-emerald-450 focus:border-emerald-500 focus:ring-emerald-500/10' : 'border-slate-200 dark:border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/10')">
                <div class="absolute inset-y-0 end-0 flex items-center pe-3">
                    <template x-if="value.length > 0 && !error">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                    <template x-if="error">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                </div>
            </div>
            <div x-show="error" x-cloak x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1"
                 class="mt-1.5 flex items-center gap-1 text-xs font-bold text-red-500">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span x-text="error"></span>
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        {{-- Email --}}
        <div x-data="{
            value: '{{ old('email') }}',
            touched: false,
            get valid() { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value); },
            get error() { return this.touched && this.value.length > 0 && !this.valid ? '{{ __('global.email_invalid') }}' : (this.touched && this.value.length === 0 ? '{{ __('global.field_required') }}' : ''); }
        }" class="group">
            <x-input-label for="email" :value="__('global.email')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 transition-colors duration-200" :class="error ? 'text-red-400' : (valid && value.length > 0 ? 'text-emerald-500' : 'text-slate-400')" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <input id="email" type="email" name="email" x-model="value" @blur="touched = true" required autocomplete="username"
                       placeholder="you@example.com"
                       class="block w-full ps-10 pe-10 rounded-xl border bg-white/70 dark:bg-gray-900/70 text-sm transition-all duration-300 py-2.5 outline-none focus:outline-none focus:ring-4 shadow-sm"
                       :class="error ? 'border-red-450 focus:border-red-500 focus:ring-red-500/10' : (valid && value.length > 0 ? 'border-emerald-450 focus:border-emerald-500 focus:ring-emerald-500/10' : 'border-slate-200 dark:border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/10')">
                <div class="absolute inset-y-0 end-0 flex items-center pe-3">
                    <template x-if="value.length > 0 && !error">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                    <template x-if="error">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                </div>
            </div>
            <div x-show="error" x-cloak x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1"
                 class="mt-1.5 flex items-center gap-1 text-xs font-bold text-red-500">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span x-text="error"></span>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        {{-- Phone --}}
        <div x-data="{
            value: '{{ old('phone') }}',
            touched: false,
            get digits() { return this.value.replace(/\D/g, ''); },
            get valid() {
                const d = this.digits;
                return d.length === 11 && /^01\d{9}$/.test(d);
            },
            get error() { return this.touched && this.value.length > 0 && !this.valid ? '{{ __('global.phone_invalid') }}' : ''; },
            formatInput() {
                const d = this.digits;
                if (d.length > 11) { this.value = d.slice(0, 11); return; }
                this.value = d;
            }
        }" class="group">
            <x-input-label for="phone" :value="__('global.phone')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none z-10">
                    <span class="text-xs font-black text-slate-400" :class="valid && value.length > 0 ? 'text-emerald-500' : ''">+20</span>
                </div>
                <input id="phone" type="tel" name="phone" x-model="value" @input="formatInput(); touched = true" @blur="touched = true"
                       autocomplete="tel"
                       placeholder="01012345678"
                       inputmode="numeric"
                       class="block w-full ps-12 pe-10 rounded-xl border bg-white/70 dark:bg-gray-900/70 text-sm transition-all duration-300 py-2.5 outline-none focus:outline-none focus:ring-4 shadow-sm tracking-wider font-mono"
                       :class="error ? 'border-red-450 focus:border-red-500 focus:ring-red-500/10' : (valid && value.length > 0 ? 'border-emerald-450 focus:border-emerald-500 focus:ring-emerald-500/10' : 'border-slate-200 dark:border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/10')">
                <div class="absolute inset-y-0 end-0 flex items-center pe-3">
                    <template x-if="value.length > 0 && !error">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                    <template x-if="error">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                </div>
            </div>
            <p x-show="value.length > 0" class="mt-1 text-[10px] font-mono font-bold text-slate-400 bg-slate-50 dark:bg-slate-800/50 rounded-lg px-2.5 py-1 inline-block">
                <span class="text-slate-300">+20 </span>
                <span x-text="'01' + value.slice(2).replace(/(\d{3})(\d{0,4})/, '$1 $2')" class="text-slate-500"></span>
            </p>
            <div x-show="error" x-cloak x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1"
                 class="mt-1.5 flex items-center gap-1 text-xs font-bold text-red-500">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span x-text="error"></span>
            </div>
            <p x-show="value.length === 0" class="mt-1 text-[10px] font-medium text-slate-400 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ __('global.phone_hint') }}
            </p>
            <x-input-error :messages="$errors->get('phone')" class="mt-1" />
        </div>

        {{-- Password + Confirm --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
                    <input id="password" name="password" x-model="value" @blur="touched = true" x-bind:type="show ? 'text' : 'password'" required autocomplete="new-password"
                           placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;"
                       class="block w-full pe-10 rounded-xl border bg-white/70 dark:bg-gray-900/70 text-sm transition-all duration-300 py-2.5 outline-none focus:outline-none focus:ring-4 shadow-sm ps-3"
                       :class="error ? 'border-red-450 focus:border-red-500 focus:ring-red-500/10' : (valid && value.length > 0 ? 'border-emerald-450 focus:border-emerald-500 focus:ring-emerald-500/10' : 'border-slate-200 dark:border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/10')">
                    <button type="button" @click="show = !show" class="absolute inset-y-0 end-0 flex items-center pe-3 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none">
                        <template x-if="!show">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </template>
                        <template x-if="show">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.965 9.965 0 012.223-3.57M6.1 6.1A9.966 9.966 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.985 9.985 0 01-4.12 5.2M3 3l18 18"/></svg>
                        </template>
                    </button>
                </div>
                {{-- Strength bars --}}
                <div x-show="value.length > 0" x-transition class="mt-1.5">
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
                </div>
                <div x-show="error" x-cloak x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     class="mt-1.5 flex items-center gap-1 text-xs font-bold text-red-500">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span x-text="error"></span>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            {{-- Confirm Password --}}
            <div x-data="{
                value: '',
                touched: false,
                show: false,
                get passwordVal() { return document.getElementById('password')?.value || ''; },
                get valid() { return this.value.length > 0 && this.value === this.passwordVal; },
                get error() { return this.touched && this.value.length > 0 && !this.valid ? '{{ __('global.password_mismatch') }}' : (this.touched && this.value.length === 0 ? '{{ __('global.field_required') }}' : ''); }
            }" class="group">
                <x-input-label for="password_confirmation" :value="__('global.confirm_password')" />
                <div class="relative mt-1">
                    <input id="password_confirmation" name="password_confirmation" x-model="value" @blur="touched = true" x-bind:type="show ? 'text' : 'password'" required autocomplete="new-password"
                           placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;"
                       class="block w-full pe-10 rounded-xl border bg-white/70 dark:bg-gray-900/70 text-sm transition-all duration-300 py-2.5 outline-none focus:outline-none focus:ring-4 shadow-sm ps-3"
                       :class="error ? 'border-red-450 focus:border-red-500 focus:ring-red-500/10' : (valid && value.length > 0 ? 'border-emerald-450 focus:border-emerald-500 focus:ring-emerald-500/10' : 'border-slate-200 dark:border-slate-800 focus:border-indigo-500 focus:ring-indigo-500/10')">
                    <button type="button" @click="show = !show" class="absolute inset-y-0 end-0 flex items-center pe-3 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none">
                        <template x-if="!show">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </template>
                        <template x-if="show">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.965 9.965 0 012.223-3.57M6.1 6.1A9.966 9.966 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.985 9.985 0 01-4.12 5.2M3 3l18 18"/></svg>
                        </template>
                    </button>
                </div>
                {{-- Match indicator --}}
                <div x-show="value.length > 0" x-transition class="mt-1.5">
                    <p class="text-[10px] font-semibold flex items-center gap-1"
                       :class="valid ? 'text-emerald-500' : 'text-amber-500'">
                        <template x-if="valid">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </template>
                        <template x-if="!valid">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </template>
                        <span x-text="valid ? '{{ __('global.password_match') }}' : '{{ __('global.password_mismatch') }}'"></span>
                    </p>
                </div>
                <div x-show="error" x-cloak x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     class="mt-1.5 flex items-center gap-1 text-xs font-bold text-red-500">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span x-text="error"></span>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-extrabold py-3 text-sm shadow-lg shadow-indigo-200 dark:shadow-indigo-900/30 hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
            <span class="flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                {{ __('global.sign_up') }}
            </span>
        </button>

        {{-- Login CTA --}}
        <p class="text-center text-xs font-semibold text-slate-500 dark:text-slate-400">
            {{ __('global.already_registered') }}
            <a href="{{ route('login') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 hover:underline font-bold transition">
                {{ __('global.sign_in') }}
            </a>
        </p>
    </form>
</x-guest-layout>