@extends('admin.layouts.app')
@section('page-title', __('global.pos_title'))
<style>
[x-cloak] { display: none !important; }
.pos-toast { animation: slideIn 0.3s ease-out; }
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
@keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
.receipt-enter { animation: fadeIn 0.25s ease-out; }
@media print { body * { visibility: hidden; } #receipt-area, #receipt-area * { visibility: visible; } #receipt-area { position: fixed; top: 0; left: 0; width: 80mm; padding: 10px; font-size: 12px; } }
</style>
@section('content')
<div x-data="posApp()">
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-8rem)]">
    {{-- Products Panel --}}
    <div class="lg:col-span-2 flex flex-col gap-3 min-h-0">
        {{-- Search + Autocomplete Dropdown --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-3 relative">
            <div class="flex gap-2 items-center">
                <div class="relative flex-1">
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="search" @input.debounce.300ms="searchProducts()" @keydown.enter.prevent="searchProducts()" @keydown.escape="closeAutocomplete()" @focus="showAutocomplete = true" @click.away="showAutocomplete = false" x-ref="searchInput"
                           placeholder="{{ __('global.pos_search_placeholder') }}"
                           class="w-full border border-slate-200 dark:border-slate-600 rounded-lg pr-10 pl-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-primary dark:bg-gray-700 dark:text-white dark:border-slate-600">

                    {{-- Autocomplete Dropdown --}}
                    <div x-show="showAutocomplete && products.length > 0" x-cloak
                         class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-slate-200 dark:border-slate-600 rounded-xl shadow-2xl z-30 max-h-80 overflow-y-auto">
                        <template x-for="(product, idx) in products.slice(0, 10)" :key="product.id">
                            <div @click="selectProduct(product); showAutocomplete = false"
                                 class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer border-b border-slate-100 dark:border-slate-700 last:border-0 transition"
                                 :class="{ 'bg-slate-50 dark:bg-slate-700/50': idx === autocompleteIndex }"
                                 @mouseenter="autocompleteIndex = idx">
                                <div class="w-8 h-8 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-700 flex-shrink-0">
                                    <img :src="product.image || '/images/logo.svg'" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold truncate" x-text="product.name"></p>
                                    <p class="text-[10px] text-slate-400 truncate">
                                        <span x-text="product.variants?.[0]?.sku || ''"></span>
                                        <span x-show="product.variants?.length > 1" class="text-slate-300"> +<span x-text="product.variants.length - 1"></span> {{ __('global.pos_more_variants') }}</span>
                                    </p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <template x-if="product.is_on_sale">
                                        <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 block" x-text="formatPrice(product.current_price)"></span>
                                    </template>
                                    <span class="text-xs font-bold text-slate-500" :class="product.is_on_sale ? 'line-through' : ''" x-text="formatPrice(product.base_price)"></span>
                                </div>
                            </div>
                        </template>
                        <div x-show="products.length > 10" class="px-3 py-2 text-[10px] text-slate-400 text-center border-t border-slate-100 dark:border-slate-700">
                            + <span x-text="products.length - 10"></span> {{ __('global.pos_more_results') }}
                        </div>
                    </div>
                </div>
                <button @click="searchProducts()" :disabled="loading" class="bg-brand-primary text-white px-4 py-2.5 rounded-lg hover:bg-brand-hover transition font-bold text-sm disabled:opacity-50 whitespace-nowrap">
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span class="hidden sm:inline mr-1">{{ __('global.search') }}</span>
                </button>
            </div>
            <div class="flex gap-3 mt-1.5 text-[10px] text-slate-400 dark:text-slate-500">
                <span class="flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg> {{ __('global.pos_type_to_search') }}</span>
                <span>⎋ {{ __('global.pos_esc') }}</span>
                <span>📷 {{ __('global.pos_barcode') }}</span>
            </div>
        </div>

        {{-- Category Tabs --}}
        <div class="flex gap-1.5 overflow-x-auto pb-1 scrollbar-thin" x-show="categories.length > 0">
            <template x-for="cat in categories" :key="cat.id">
                <button @click="filterCategory = (filterCategory === cat.id ? null : cat.id); searchProducts();"
                        class="px-3 py-1.5 text-xs font-bold rounded-lg border transition-all whitespace-nowrap flex-shrink-0"
                        :class="filterCategory === cat.id ? 'bg-brand-primary text-white border-brand-primary' : 'bg-white dark:bg-gray-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 hover:border-brand-primary/40'"
                        x-text="cat.name">
                </button>
            </template>
        </div>

        {{-- Product Grid --}}
        <div class="flex-1 overflow-y-auto min-h-0">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3" x-show="products.length > 0">
                <template x-for="product in products" :key="product.id">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 overflow-hidden hover:shadow-md hover:-translate-y-1 transition-all duration-300 cursor-pointer relative"
                         @click="selectProduct(product)">
                        {{-- Sale badge --}}
                        <div x-show="product.is_on_sale" class="absolute top-1.5 right-1.5 z-10 bg-gradient-to-l from-emerald-500 to-teal-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full flex items-center gap-0.5 shadow">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            -<span x-text="Math.round((1 - product.current_price / product.base_price) * 100)"></span>%
                        </div>
                        <div class="aspect-square bg-slate-100 dark:bg-slate-700 overflow-hidden">
                            <img :src="product.image || '/images/logo.svg'" :alt="product.name" class="w-full h-full object-cover" loading="lazy">
                        </div>
                        <div class="p-2.5">
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-100 line-clamp-2 leading-tight" x-text="product.name"></p>
                            <div class="mt-1 flex items-center gap-1.5">
                                <template x-if="product.is_on_sale">
                                    <span class="text-xs font-black text-emerald-600 dark:text-emerald-400" x-text="formatPrice(product.current_price)"></span>
                                </template>
                                <span class="text-xs font-black text-brand-primary dark:text-accent" :class="product.is_on_sale ? 'text-[10px] line-through text-slate-400 dark:text-slate-500 font-bold' : ''" x-text="formatPrice(product.base_price)"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="products.length === 0 && initialLoading" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-10 text-center">
                <svg class="w-10 h-10 mx-auto text-brand-primary animate-spin mb-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <p class="text-slate-400 dark:text-slate-500 text-sm font-bold">{{ __('global.pos_loading') }}</p>
            </div>
            <div x-show="products.length === 0 && !initialLoading && searched" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-10 text-center">
                <svg class="w-14 h-14 mx-auto text-slate-200 dark:text-slate-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <p class="text-slate-500 dark:text-slate-400 text-sm font-bold">{{ __('global.pos_no_results') }}</p>
            </div>
            <div x-show="products.length === 0 && !initialLoading && !searched" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-10 text-center">
                <svg class="w-16 h-16 mx-auto text-slate-100 dark:text-slate-800 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                <p class="text-slate-400 dark:text-slate-500 text-sm font-bold">{{ __('global.pos_search_hint') }}</p>
            </div>
        </div>
    </div>

    {{-- Cart Panel --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 flex flex-col h-full sticky top-0">
        {{-- Tabs --}}
        <div class="flex border-b border-slate-200 dark:border-slate-700">
            <button @click="posTab = 'cart'"
                    class="flex-1 py-3 text-xs font-bold transition-all relative"
                    :class="posTab === 'cart' ? 'text-brand-primary dark:text-accent' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300'">
                <div class="flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    <span>{{ __('global.pos_cart') }}</span>
                    <span class="text-[10px] bg-brand-primary/10 dark:bg-accent/20 text-brand-primary dark:text-accent font-bold px-1.5 py-0.5 rounded-full" x-text="cartCount" x-show="cartCount > 0"></span>
                </div>
                <div x-show="posTab === 'cart'" class="absolute bottom-0 left-2 right-2 h-0.5 bg-brand-primary dark:bg-accent rounded-full"></div>
            </button>
            <button @click="posTab = 'orders'"
                    class="flex-1 py-3 text-xs font-bold transition-all relative"
                    :class="posTab === 'orders' ? 'text-brand-primary dark:text-accent' : 'text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300'">
                <div class="flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ __('global.pos_recent_orders') }}</span>
                </div>
                <div x-show="posTab === 'orders'" class="absolute bottom-0 left-2 right-2 h-0.5 bg-brand-primary dark:bg-accent rounded-full"></div>
            </button>
        </div>

        {{-- Cart Tab --}}
        <div x-show="posTab === 'cart'" class="flex flex-col flex-1 min-h-0">
            {{-- Cart Items --}}
            <div class="flex-1 overflow-y-auto p-3 space-y-2" x-show="cartItems.length > 0">
                <template x-for="item in cartItems" :key="item.variant_id">
                    <div class="flex gap-2.5 bg-slate-50 dark:bg-slate-700/50 rounded-xl p-2.5 border border-slate-100 dark:border-slate-700 hover:border-brand-primary/20 transition">
                        <div class="w-12 h-12 rounded-lg overflow-hidden bg-slate-200 dark:bg-slate-600 flex-shrink-0">
                            <img :src="item.image || '/images/logo.svg'" class="w-full h-full object-cover" :alt="item.product_name" loading="lazy">
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate" x-text="item.product_name"></p>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400" x-show="item.color || item.size" x-text="(item.color||'') + (item.color && item.size ? ' / ' : '') + (item.size||'')"></p>
                            <p class="text-xs font-black text-brand-primary dark:text-accent" x-text="formatPrice(item.price)"></p>
                        </div>
                        <div class="flex flex-col items-end gap-1 flex-shrink-0">
                            <div class="flex items-center gap-0.5">
                                <button @click="updateQty(item.variant_id, item.quantity - 1)" :disabled="loading" class="w-7 h-7 rounded-lg bg-slate-200 dark:bg-slate-600 hover:bg-brand-primary hover:text-white disabled:opacity-40 transition flex items-center justify-center text-sm font-bold">−</button>
                                <input type="number" :value="item.quantity" @change="updateQty(item.variant_id, parseInt($event.target.value) || 0)" class="w-8 text-center text-xs font-black bg-transparent border-0 p-0 focus:outline-none [&::-webkit-inner-spin-button]:appearance-none" min="0">
                                <button @click="updateQty(item.variant_id, item.quantity + 1)" :disabled="loading" class="w-7 h-7 rounded-lg bg-slate-200 dark:bg-slate-600 hover:bg-brand-primary hover:text-white disabled:opacity-40 transition flex items-center justify-center text-sm font-bold">+</button>
                            </div>
                            <p class="text-[10px] font-bold text-slate-500" x-text="formatPrice(item.price * item.quantity)"></p>
                            <button @click="removeItem(item.variant_id)" :disabled="loading" class="text-[9px] text-red-400 hover:text-red-600 font-bold transition">{{ __('global.pos_remove') }}</button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Empty Cart --}}
            <div class="flex-1 flex items-center justify-center" x-show="cartItems.length === 0">
                <div class="text-center p-6">
                    <svg class="w-14 h-14 mx-auto text-slate-100 dark:text-slate-800 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    <p class="text-sm text-slate-400 dark:text-slate-500 font-bold">{{ __('global.pos_cart_empty') }}</p>
                    <p class="text-xs text-slate-300 dark:text-slate-600 mt-1">{{ __('global.pos_cart_empty_hint') }}</p>
                </div>
            </div>

            {{-- Checkout Form --}}
            <div class="border-t border-slate-200 dark:border-slate-700 p-4 space-y-3" x-show="cartItems.length > 0">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-slate-600 dark:text-slate-300">{{ __('global.pos_total') }}</span>
                    <span class="text-xl font-black text-brand-primary dark:text-accent" x-text="formatPrice(total)"></span>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <input type="text" x-model="customerName" placeholder="{{ __('global.pos_customer_name') }}"
                           class="border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-primary dark:bg-gray-700 dark:text-white">
                    <input type="tel" x-model="customerPhone" placeholder="{{ __('global.pos_customer_phone') }}"
                           class="border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-primary dark:bg-gray-700 dark:text-white">
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <template x-for="method in ['cash', 'card', 'wallet']" :key="method">
                        <button @click="paymentMethod = method"
                                class="py-2 rounded-lg border-2 text-xs font-bold transition-all"
                                :class="paymentMethod === method ? 'border-brand-primary bg-brand-primary/10 text-brand-primary dark:border-accent dark:bg-accent/20 dark:text-accent' : 'border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:border-brand-primary/40'">
                            <span x-text="method === 'cash' ? '{{ __('global.cash') }}' : method === 'card' ? '{{ __('global.card') }}' : '{{ __('global.wallet') }}'"></span>
                        </button>
                    </template>
                </div>

                <textarea x-model="notes" rows="1" placeholder="{{ __('global.pos_notes') }}" class="w-full border border-slate-200 dark:border-slate-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-primary dark:bg-gray-700 dark:text-white resize-none"></textarea>

                <button @click="checkout()" :disabled="loading || cartItems.length === 0"
                        class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold py-3 rounded-xl transition-all duration-300 active:scale-[0.97] shadow-lg flex items-center justify-center gap-2 text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <template x-if="!loading">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                    <template x-if="loading">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </template>
                    <span x-text="loading ? '{{ __('global.pos_processing') }}...' : '{{ __('global.pos_complete_sale') }}'"></span>
                </button>

                <button @click="clearCart()" :disabled="loading" class="block w-full text-center text-xs text-red-400 hover:text-red-600 font-bold transition disabled:opacity-40">
                    {{ __('global.pos_clear_cart') }}
                </button>
            </div>
        </div>

        {{-- Orders Tab --}}
        <div x-show="posTab === 'orders'" class="flex flex-col flex-1 min-h-0">
            <div class="flex-1 overflow-y-auto p-3 space-y-2">
                <template x-for="order in recentOrders" :key="order.id">
                    <div class="bg-slate-50 dark:bg-slate-700/30 rounded-xl p-3 border border-slate-100 dark:border-slate-700 hover:border-brand-primary/20 transition cursor-pointer"
                         @click="viewOrder(order.id)">
                        <div class="flex items-start justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-black text-slate-800 dark:text-slate-100">#<span x-text="order.id"></span></span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold"
                                          :class="order.order_type === 'offline' ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300' : 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300'"
                                          x-text="order.order_type === 'offline' ? '{{ __('global.admin_offline') }}' : '{{ __('global.admin_online') }}'"></span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5" x-text="order.created_at"></p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    <span x-text="order.customer_name || '{{ __('global.pos_guest') }}'"></span>
                                    <span x-show="order.cashier_name" class="text-slate-400"> · <span class="font-medium" x-text="order.cashier_name"></span></span>
                                </p>
                            </div>
                            <div class="text-right flex-shrink-0 ml-2">
                                <p class="text-sm font-black text-brand-primary dark:text-accent" x-text="formatPrice(order.total)"></p>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="recentOrders.length === 0" class="flex items-center justify-center h-full">
                    <div class="text-center p-6">
                        <svg class="w-12 h-12 mx-auto text-slate-100 dark:text-slate-800 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <p class="text-sm text-slate-400 dark:text-slate-500 font-bold">{{ __('global.pos_no_orders') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Variant Selection Modal --}}
<div x-show="showVariantModal" x-cloak @keydown.escape.window="showVariantModal = false" @click="showVariantModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="animation: fadeIn 0.15s ease-out;">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full p-6 max-h-[85vh] overflow-y-auto" @click.stop>
        <div class="flex items-start gap-4 mb-5">
            <div class="w-16 h-16 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-700 flex-shrink-0">
                <img :src="selectedVariantImage" class="w-full h-full object-cover">
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-extrabold text-slate-900 dark:text-white text-lg leading-tight" x-text="selectedProduct?.name"></h3>
                <div class="flex items-center gap-2 mt-1">
                    <template x-if="selectedProduct?.is_on_sale">
                        <span class="text-sm font-black text-emerald-600 dark:text-emerald-400" x-text="formatPrice(selectedProduct.current_price)"></span>
                    </template>
                    <span class="text-sm font-black text-brand-primary dark:text-accent" :class="selectedProduct?.is_on_sale ? 'text-xs line-through text-slate-400' : ''" x-text="formatPrice(selectedProduct?.base_price)"></span>
                </div>
            </div>
            <button @click="showVariantModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <template x-if="selectedProduct?.has_variants">
            <div>
                <div class="mb-4" x-show="uniqueColors.length > 0">
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">{{ __('global.admin_color') }}</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="v in uniqueColors" :key="v">
                            <button @click="pickColor(v)"
                                    class="px-3 py-1.5 text-xs font-bold rounded-lg border-2 transition-all"
                                    :class="selectedColor === v ? 'border-brand-primary bg-brand-primary/10 text-brand-primary dark:border-accent dark:bg-accent/20 dark:text-accent' : 'border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-brand-primary/40'"
                                    x-text="v"></button>
                        </template>
                    </div>
                </div>
                <div class="mb-4" x-show="uniqueSizes.length > 0">
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">{{ __('global.admin_size') }}</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="v in uniqueSizes" :key="v">
                            <button @click="pickSize(v)"
                                    class="px-3 py-1.5 text-xs font-bold rounded-lg border-2 transition-all"
                                    :class="selectedSize === v ? 'border-brand-primary bg-brand-primary/10 text-brand-primary dark:border-accent dark:bg-accent/20 dark:text-accent' : 'border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-brand-primary/40'"
                                    x-text="v"></button>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="!selectedProduct?.has_variants">
            <div class="mb-4 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('global.pos_simple_product') }}</p>
            </div>
        </template>

        {{-- Stock info --}}
        <div class="mb-4 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
            <div class="flex items-center justify-between">
                <label class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ __('global.pos_quantity') }}</label>
                <span class="text-[10px] text-slate-500" x-show="selectedVariant">
                    {{ __('global.pos_stock_available') }}: <span class="font-bold" x-text="selectedVariant?.stock || 0"></span>
                </span>
            </div>
            <div class="flex items-center gap-3 mt-2">
                <button @click="qty = Math.max(1, qty - 1)" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 hover:bg-brand-primary hover:text-white transition flex items-center justify-center font-bold text-lg">−</button>
                <input type="number" x-model="qty" @input="qty = Math.max(1, Math.min(maxStock, parseInt($event.target.value) || 1))" class="w-16 text-center text-xl font-black bg-transparent border-0 p-0 focus:outline-none focus:ring-0 [&::-webkit-inner-spin-button]:appearance-none">
                <button @click="qty = Math.min(maxStock, qty + 1)" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 hover:bg-brand-primary hover:text-white transition flex items-center justify-center font-bold text-lg">+</button>
            </div>
        </div>

        <div class="flex gap-3">
            <button @click="showVariantModal = false" class="flex-1 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                {{ __('global.cancel') }}
            </button>
            <button @click="addToCart()" :disabled="!canAddToCart || loading"
                    class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-brand-primary to-accent text-white text-sm font-extrabold hover:from-brand-hover hover:to-accent-hover transition shadow-lg disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <template x-if="loading">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </template>
                <span x-text="canAddToCart ? '{{ __('global.pos_add_to_cart') }}' : '{{ __('global.pos_select_options') }}'"></span>
            </button>
        </div>
    </div>
</div>

{{-- Receipt Modal --}}
<div x-show="showReceipt" x-cloak @keydown.escape.window="showReceipt = false" @click="showReceipt = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="animation: fadeIn 0.2s ease-out;">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-6 max-h-[90vh] overflow-y-auto receipt-enter" @click.stop>
        <div id="receipt-area" class="text-center">
            {{-- Store Header --}}
            <div class="border-b-2 border-dashed border-slate-200 dark:border-slate-700 pb-3 mb-3">
                <h2 class="font-black text-lg">{{ config('app.name') }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400" x-text="receipt.store_address"></p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Tel: <span x-text="receipt.store_phone"></span></p>
            </div>

            {{-- Receipt Info --}}
            <div class="text-left text-xs space-y-0.5 mb-3">
                <p class="flex justify-between"><span class="text-slate-500">{{ __('global.invoice_no') }}</span> <span class="font-bold" x-text="'#' + receipt.order_id"></span></p>
                <p class="flex justify-between"><span class="text-slate-500">{{ __('global.invoice_date') }}</span> <span class="font-bold" x-text="receipt.date"></span></p>
                <p class="flex justify-between"><span class="text-slate-500">Cashier</span> <span class="font-bold" x-text="receipt.cashier"></span></p>
                <p class="flex justify-between" x-show="receipt.customer"><span class="text-slate-500">{{ __('global.invoice_customer_info') }}</span> <span class="font-bold" x-text="receipt.customer"></span></p>
            </div>

            {{-- Items --}}
            <table class="w-full text-xs mb-3">
                <thead>
                    <tr class="border-t border-b border-slate-200 dark:border-slate-700">
                        <th class="text-left py-1 font-bold text-slate-500">{{ __('global.invoice_products') }}</th>
                        <th class="text-center py-1 font-bold text-slate-500">{{ __('global.invoice_qty') }}</th>
                        <th class="text-right py-1 font-bold text-slate-500">{{ __('global.invoice_total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="item in receipt.items" :key="item.variant_id">
                        <tr>
                            <td class="py-1 text-left">
                                <span x-text="item.product_name"></span>
                                <span x-show="item.color || item.size" class="text-slate-400" x-text="' (' + (item.color||'') + (item.color && item.size ? ' / ' : '') + (item.size||'') + ')'"></span>
                            </td>
                            <td class="py-1 text-center" x-text="item.quantity"></td>
                            <td class="py-1 text-right font-bold" x-text="formatPrice(item.price * item.quantity)"></td>
                        </tr>
                    </template>
                </tbody>
                <tfoot>
                    <tr class="border-t border-slate-200 dark:border-slate-700">
                        <td colspan="2" class="py-1 text-left font-bold">{{ __('global.pos_total') }}</td>
                        <td class="py-1 text-right font-black text-lg" x-text="formatPrice(receipt.total)"></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-left text-slate-500">{{ __('global.pos_payment') }}: <span class="font-bold text-slate-700 dark:text-slate-200" x-text="receipt.payment_method"></span></td>
                        <td class="text-right text-slate-500 text-[10px]" x-text="receipt.paid_at"></td>
                    </tr>
                </tfoot>
            </table>

            {{-- Thank You --}}
            <div class="border-t-2 border-dashed border-slate-200 dark:border-slate-700 pt-3">
                <p class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ __('global.invoice_thanks') }}</p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3 mt-5">
            <button @click="showReceipt = false" class="flex-1 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                {{ __('global.close') }}
            </button>
            <button @click="printReceipt()" class="flex-1 py-2.5 rounded-xl bg-brand-primary text-white text-sm font-extrabold hover:bg-brand-hover transition shadow-lg flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                {{ __('global.invoice_print') }}
            </button>
        </div>
    </div>
</div>

{{-- Toast Notifications --}}
<div class="fixed top-4 right-4 z-[100] space-y-2 pointer-events-none">
    <template x-for="(toast, i) in toasts" :key="i">
        <div class="pos-toast pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl border text-sm font-bold transition-all cursor-pointer max-w-sm"
             :class="toast.type === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/80 border-emerald-200 dark:border-emerald-700 text-emerald-800 dark:text-emerald-200' : 'bg-red-50 dark:bg-red-900/80 border-red-200 dark:border-red-700 text-red-800 dark:text-red-200'"
             @click="removeToast(i)">
            <template x-if="toast.type === 'success'">
                <svg class="w-5 h-5 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </template>
            <template x-if="toast.type === 'error'">
                <svg class="w-5 h-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </template>
            <span x-text="toast.message" class="flex-1"></span>
        </div>
    </template>
</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('posApp', () => ({
        // State
        search: '',
        searched: false,
        initialLoading: true,
        products: [],
        categories: [],
        filterCategory: null,
        showAutocomplete: false,
        autocompleteIndex: -1,
        cartItems: [],
        recentOrders: [],
        posTab: 'cart',
        total: 0,
        cartCount: 0,
        loading: false,
        toasts: [],

        // Variant modal
        showVariantModal: false,
        selectedProduct: null,
        selectedColor: null,
        selectedSize: null,
        qty: 1,
        maxStock: 999,

        // Select color and clear size if incompatible
        pickColor(color) {
            this.selectedColor = color;
            const sizesWithColor = [...new Set(this.selectedProduct.variants
                .filter(v => v.color === color).map(v => v.size).filter(Boolean))];
            if (this.selectedSize && !sizesWithColor.includes(this.selectedSize)) {
                this.selectedSize = sizesWithColor.length > 0 ? sizesWithColor[0] : null;
            }
        },

        // Select size and clear color if incompatible
        pickSize(size) {
            this.selectedSize = size;
            const colorsWithSize = [...new Set(this.selectedProduct.variants
                .filter(v => v.size === size).map(v => v.color).filter(Boolean))];
            if (this.selectedColor && !colorsWithSize.includes(this.selectedColor)) {
                this.selectedColor = colorsWithSize.length > 0 ? colorsWithSize[0] : null;
            }
        },

        // Get variant image matching selected color/size
        get selectedVariantImage() {
            if (!this.selectedProduct) return '/images/logo.svg';
            const v = this.selectedVariant;
            if (v && v.image) return v.image;
            if (this.selectedColor) {
                const match = this.selectedProduct.variants?.find(v => v.color === this.selectedColor && v.image);
                if (match) return match.image;
            }
            return this.selectedProduct.image || '/images/logo.svg';
        },

        // Checkout form
        customerName: '',
        customerPhone: '',
        paymentMethod: 'cash',
        notes: '',

        // Receipt
        showReceipt: false,
        receipt: { items: [] },

        // Barcode buffer
        barcodeBuffer: '',
        barcodeTimer: null,

        init() {
            this.loadCart();
            this.loadCategories();
            this.loadRecentOrders();
            this.initialLoading = true;
            this.loadAllProducts();
            this.focusSearch();

            // Barcode scanner: listen for rapid keydown
            document.addEventListener('keydown', (e) => {
                if (this.showVariantModal || this.showReceipt) return;
                if (e.key === 'Enter' && this.barcodeBuffer.length > 3) {
                    this.search = this.barcodeBuffer;
                    this.barcodeBuffer = '';
                    this.searchProducts();
                    return;
                }
                if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey && document.activeElement?.tagName !== 'INPUT' && document.activeElement?.tagName !== 'TEXTAREA') {
                    this.barcodeBuffer += e.key;
                    clearTimeout(this.barcodeTimer);
                    this.barcodeTimer = setTimeout(() => { this.barcodeBuffer = ''; }, 200);
                }
            });

            // Real-time stock updates
            window.addEventListener('stock-updated', (e) => this.handleStockUpdate(e.detail));
        },

        handleStockUpdate(data) {
            for (const product of this.products) {
                for (const variant of (product.variants || [])) {
                    if (variant.id === data.variant_id) {
                        variant.stock = data.stock_after;
                        return;
                    }
                }
            }
        },

        focusSearch() {
            this.$nextTick(() => this.$refs?.searchInput?.focus());
        },

        closeAutocomplete() {
            this.showAutocomplete = false;
            this.autocompleteIndex = -1;
            this.search = '';
            this.products = [];
            this.searched = false;
        },

        async loadAllProducts() {
            try {
                const res = await fetch(`{{ route('admin.pos.search') }}?limit=50`);
                this.products = await res.json();
            } catch (e) {}
            finally { this.initialLoading = false; }
        },

        // --- Toast ---
        addToast(type, message) {
            this.toasts.push({ type, message });
            setTimeout(() => { this.removeToast(0); }, 4000);
        },
        removeToast(i) {
            this.toasts.splice(i, 1);
        },

        // --- Categories ---
        async loadCategories() {
            try {
                const res = await fetch('{{ route('admin.pos.categories') }}');
                this.categories = await res.json();
            } catch (e) {}
        },

        // --- Recent Orders ---
        async loadRecentOrders() {
            try {
                const res = await fetch('{{ route('admin.pos.recent') }}');
                this.recentOrders = await res.json();
            } catch (e) {}
        },

        viewOrder(orderId) {
            window.location.href = `{{ url('admin/orders') }}/${orderId}`;
        },

        // --- Search ---
        async searchProducts() {
            this.searched = !!this.search.trim();
            this.showAutocomplete = this.searched;
            this.autocompleteIndex = -1;
            this.loading = true;
            try {
                let url = `{{ route('admin.pos.search') }}?search=${encodeURIComponent(this.search)}&limit=50`;
                if (this.filterCategory) url += `&category_id=${this.filterCategory}`;
                const res = await fetch(url);
                this.products = await res.json();
            } catch (e) {
                this.addToast('error', '{{ __('global.pos_search_error') }}');
            } finally {
                this.loading = false;
            }
        },

        // --- Product Selection ---
        selectProduct(product) {
            this.selectedProduct = product;
            this.selectedColor = null;
            this.selectedSize = null;
            this.qty = 1;
            this.maxStock = 999;

            if (!product.has_variants && product.variants.length > 0) {
                this.selectedColor = product.variants[0].color;
                this.selectedSize = product.variants[0].size;
                this.maxStock = product.variants[0].stock;
            } else if (product.has_variants) {
                this.maxStock = Math.max(...product.variants.map(v => v.stock), 0);
                const colors = [...new Set(product.variants.map(v => v.color).filter(Boolean))];
                const sizes = [...new Set(product.variants.map(v => v.size).filter(Boolean))];
                if (colors.length === 1) this.selectedColor = colors[0];
                if (sizes.length === 1) this.selectedSize = sizes[0];
                if (colors.length === 1 && sizes.length === 1) {
                    const match = product.variants.find(v => v.color === colors[0] && v.size === sizes[0]);
                    if (match) this.maxStock = match.stock;
                }
            }

            this.showVariantModal = true;
        },

        get uniqueColors() {
            if (!this.selectedProduct) return [];
            let variants = this.selectedProduct.variants;
            if (this.selectedSize) {
                variants = variants.filter(v => v.size === this.selectedSize);
            }
            return [...new Set(variants.map(v => v.color).filter(Boolean))];
        },

        get uniqueSizes() {
            if (!this.selectedProduct) return [];
            let variants = this.selectedProduct.variants;
            if (this.selectedColor) {
                variants = variants.filter(v => v.color === this.selectedColor);
            }
            return [...new Set(variants.map(v => v.size).filter(Boolean))];
        },

        get selectedVariant() {
            if (!this.selectedProduct) return null;
            return this.selectedProduct.variants.find(v =>
                (!this.selectedColor || v.color === this.selectedColor) &&
                (!this.selectedSize || v.size === this.selectedSize)
            );
        },

        get canAddToCart() {
            if (!this.selectedProduct) return false;
            if (this.selectedProduct.has_variants) {
                return this.selectedVariant !== null && this.selectedVariant !== undefined;
            }
            return this.selectedProduct.variants.length > 0;
        },

        // --- Add to Cart (AJAX) ---
        async addToCart() {
            if (!this.canAddToCart) return;
            const variant = this.selectedVariant || this.selectedProduct.variants[0];
            if (!variant) return;

            this.loading = true;
            try {
                const res = await fetch('{{ route('admin.pos.add') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ variant_id: variant.id, quantity: this.qty })
                });
                const data = await res.json();
                if (!res.ok) { this.addToast('error', data.error || '{{ __('global.pos_error') }}'); return; }
                this.cartItems = Object.values(data.cart || {});
                this.total = data.total || 0;
                this.cartCount = this.cartItems.length;
                this.showVariantModal = false;
                this.showAutocomplete = false;
                this.search = '';
                this.loadAllProducts();
                this.focusSearch();
                this.addToast('success', '{{ __('global.pos_added') }}');
            } catch (e) {
                this.addToast('error', '{{ __('global.pos_error') }}');
            } finally {
                this.loading = false;
            }
        },

        // --- Update Qty (AJAX) ---
        async updateQty(variantId, qty) {
            if (qty < 0) return;
            this.loading = true;
            try {
                const res = await fetch('{{ route('admin.pos.update') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ variant_id: variantId, quantity: qty })
                });
                const data = await res.json();
                if (!res.ok) { this.addToast('error', data.error || '{{ __('global.pos_error') }}'); return; }
                this.cartItems = Object.values(data.cart || {});
                this.total = data.total || 0;
                this.cartCount = this.cartItems.length;
            } catch (e) {
                this.addToast('error', '{{ __('global.pos_error') }}');
            } finally {
                this.loading = false;
            }
        },

        // --- Remove from Cart (AJAX) ---
        async removeItem(variantId) {
            this.loading = true;
            try {
                const res = await fetch(`/admin/pos/remove/${variantId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                const data = await res.json();
                if (!res.ok) { this.addToast('error', '{{ __('global.pos_error') }}'); return; }
                this.cartItems = Object.values(data.cart || {});
                this.total = data.total || 0;
                this.cartCount = this.cartItems.length;
            } catch (e) {
                this.addToast('error', '{{ __('global.pos_error') }}');
            } finally {
                this.loading = false;
            }
        },

        // --- Checkout (AJAX) ---
        async checkout() {
            if (this.cartItems.length === 0) return;

            if (!this.customerName.trim() && !this.customerPhone.trim()) {
                this.addToast('error', '{{ __('global.pos_enter_customer') }}');
                return;
            }

            this.loading = true;
            try {
                const res = await fetch('{{ route('admin.pos.checkout') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        name: this.customerName.trim(),
                        phone: this.customerPhone.trim(),
                        payment_method: this.paymentMethod,
                        notes: this.notes,
                    })
                });
                const data = await res.json();
                if (!res.ok) {
                    const msg = data.error || (data.errors ? Object.values(data.errors).flat().join(' - ') : null) || data.message || '{{ __('global.pos_checkout_error') }}';
                    this.addToast('error', msg);
                    return;
                }

                this.receipt = data.receipt || data;
                this.showReceipt = true;
                this.addToast('success', data.message || '{{ __('global.pos_checkout_success') }}');

                this.cartItems = [];
                this.total = 0;
                this.cartCount = 0;
                this.customerName = '';
                this.customerPhone = '';
                this.notes = '';
                this.search = '';
                this.showAutocomplete = false;
                this.loadAllProducts();
                this.loadRecentOrders();
                this.focusSearch();
            } catch (e) {
                this.addToast('error', '{{ __('global.pos_checkout_error') }}');
            } finally {
                this.loading = false;
            }
        },

        // --- Clear Cart (AJAX) ---
        async clearCart() {
            this.loading = true;
            try {
                await fetch('{{ route('admin.pos.clear') }}');
                this.cartItems = [];
                this.total = 0;
                this.cartCount = 0;
                this.customerName = '';
                this.customerPhone = '';
                this.notes = '';
                this.addToast('success', '{{ __('global.pos_cart_cleared') }}');
            } catch (e) {} finally {
                this.loading = false;
            }
        },

        // --- Load Cart ---
        async loadCart() {
            try {
                const res = await fetch('{{ route('admin.pos.cart') }}');
                const data = await res.json();
                this.cartItems = Object.values(data.cart || {});
                this.total = data.total || 0;
                this.cartCount = this.cartItems.length;
            } catch (e) {}
        },

        // --- Print Receipt ---
        printReceipt() {
            window.print();
        },

        // --- Format Price ---
        formatPrice(price) {
            const value = Math.round(parseFloat(price || 0));
            return new Intl.NumberFormat('en-US', {
                style: 'currency', currency: 'EGP', maximumFractionDigits: 0
            }).format(value);
        }
    }));
});
</script>
@endpush
