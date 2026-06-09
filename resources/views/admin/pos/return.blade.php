@extends('admin.layouts.app')
@section('page-title', __('global.pos_return_title'))
<style>
[x-cloak] { display: none !important; }
</style>
@section('content')
<div x-data="posReturnApp()" class="space-y-6">
    {{-- Search Section --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-4">
        <div class="flex gap-2 items-center">
            <div class="relative flex-1">
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="searchQuery" @keydown.enter.prevent="searchOrders()" x-ref="searchInput"
                       placeholder="{{ __('global.pos_return_search_placeholder') }}"
                       class="w-full border border-slate-200 dark:border-slate-600 rounded-lg pr-10 pl-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-primary dark:bg-gray-700 dark:text-white dark:border-slate-600">
            </div>
            <button @click="searchOrders()" :disabled="searching || !searchQuery.trim()"
                    class="bg-brand-primary text-white px-5 py-2.5 rounded-lg hover:bg-brand-hover transition font-bold text-sm disabled:opacity-50 whitespace-nowrap flex items-center gap-2">
                <template x-if="searching">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </template>
                <span x-text="searching ? '{{ __('global.pos_return_searching') }}' : '{{ __('global.pos_return_search_btn') }}'"></span>
            </button>
        </div>

        {{-- Search hint --}}
        <p class="text-[10px] text-slate-400 mt-1.5">{{ __('global.pos_return_search_placeholder') }}</p>
    </div>

    {{-- Order Results / Selected Order --}}
    <div x-show="searched && orders.length === 0 && !searching" x-cloak
         class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-10 text-center">
        <svg class="w-14 h-14 mx-auto text-slate-200 dark:text-slate-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <p class="text-slate-500 dark:text-slate-400 font-bold">{{ __('global.pos_return_no_orders') }}</p>
    </div>

    {{-- Order List (before selection) --}}
    <div x-show="orders.length > 0 && !selectedOrder" x-cloak class="space-y-2">
        <template x-for="order in orders" :key="order.id">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-4 hover:shadow-md transition cursor-pointer"
                 @click="selectOrder(order)">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <span class="text-lg font-black text-brand-primary">#<span x-text="order.id"></span></span>
                        <div>
                            <p class="font-bold text-sm" x-text="order.customer_name"></p>
                            <p class="text-xs text-slate-500" x-text="order.date"></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-black" x-text="formatPrice(order.total)"></p>
                        <p class="text-[10px] text-slate-500" x-text="order.items.length + ' items'"></p>
                    </div>
                </div>
                <div class="mt-2 flex gap-2 text-[10px]">
                    <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300" x-text="'{{ __('global.pos_return_cashier') }}: ' + (order.cashier || '—')"></span>
                    <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300" x-text="'{{ __('global.pos_return_payment') }}: ' + order.payment_method"></span>
                    <span class="px-2 py-0.5 rounded-full" :class="order.has_returnable ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600'" x-text="order.has_returnable ? '{{ __('global.pos_return_returnable') }}' : '{{ __('global.pos_return_no_returnable') }}'"></span>
                </div>
            </div>
        </template>
        <p class="text-xs text-slate-400 text-center" x-text="orders.length + ' {{ __('global.pos_return_order_found') }}'"></p>
    </div>

    {{-- Selected Order Detail --}}
    <div x-show="selectedOrder" x-cloak class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Order info + Items to return --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Order info header --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-extrabold text-lg">{{ __('global.pos_return_order_info') }}</h3>
                    <button @click="selectedOrder = null; orders = []; searched = false"
                            class="text-xs text-slate-400 hover:text-slate-600 font-bold transition">✕ {{ __('global.close') }}</button>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                    <div><span class="text-slate-500 block">{{ __('global.pos_return_customer') }}</span><span class="font-bold" x-text="selectedOrder.customer_name"></span></div>
                    <div><span class="text-slate-500 block">{{ __('global.pos_return_cashier') }}</span><span class="font-bold" x-text="selectedOrder.cashier || '—'"></span></div>
                    <div><span class="text-slate-500 block">{{ __('global.pos_return_date') }}</span><span class="font-bold" x-text="selectedOrder.date"></span></div>
                    <div><span class="text-slate-500 block">{{ __('global.pos_return_original_total') }}</span><span class="font-bold text-brand-primary" x-text="formatPrice(selectedOrder.total)"></span></div>
                    <div><span class="text-slate-500 block">{{ __('global.pos_return_status') }}</span><span class="font-bold" x-text="selectedOrder.status"></span></div>
                    <div><span class="text-slate-500 block">{{ __('global.pos_return_payment') }}</span><span class="font-bold" x-text="selectedOrder.payment_method"></span></div>
                </div>
            </div>

            {{-- Items to return --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-extrabold">{{ __('global.pos_return_items_label') }}</h3>
                    <label class="flex items-center gap-2 text-xs">
                        <input type="checkbox" @change="toggleAllReturnItems($event.target.checked)" class="rounded border-slate-300 text-brand-primary focus:ring-brand-primary">
                        {{ __('global.pos_return_select') }}
                    </label>
                </div>

                <div x-show="selectedOrder.items.filter(i => i.returnable > 0).length === 0" class="text-center py-6 text-slate-400 text-sm">
                    {{ __('global.pos_return_no_returnable') }}
                </div>

                <template x-for="item in selectedOrder.items" :key="item.id">
                    <div x-show="item.returnable > 0"
                         class="flex items-center gap-3 py-2.5 border-b border-slate-100 dark:border-slate-700 last:border-0">
                        <input type="checkbox" :checked="returnItems[item.id] > 0"
                               @change="toggleReturnItem(item.id, $event.target.checked)"
                               class="rounded border-slate-300 text-brand-primary focus:ring-brand-primary flex-shrink-0">
                        <div class="w-10 h-10 rounded-lg overflow-hidden bg-slate-100 dark:bg-slate-700 flex-shrink-0">
                            <img :src="item.image || '/images/logo.svg'" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold truncate" x-text="item.product_name"></p>
                            <p class="text-[10px] text-slate-400" x-show="item.color || item.size" x-text="(item.color||'') + (item.color && item.size ? ' / ' : '') + (item.size||'')"></p>
                            <p class="text-xs font-black text-brand-primary" x-text="formatPrice(item.unit_price)"></p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <template x-if="returnItems[item.id] > 0">
                                <div class="flex items-center gap-1">
                                    <button @click="returnItems[item.id] = Math.max(0, returnItems[item.id] - 1)" class="w-6 h-6 rounded bg-slate-100 dark:bg-slate-700 hover:bg-brand-primary hover:text-white transition text-xs font-bold">−</button>
                                    <span class="w-6 text-center text-xs font-black" x-text="returnItems[item.id]"></span>
                                    <button @click="returnItems[item.id] = Math.min(item.returnable, returnItems[item.id] + 1)" class="w-6 h-6 rounded bg-slate-100 dark:bg-slate-700 hover:bg-brand-primary hover:text-white transition text-xs font-bold">+</button>
                                </div>
                            </template>
                            <template x-if="!returnItems[item.id]">
                                <span class="text-[10px] text-slate-400" x-text="'{{ __('global.pos_return_returnable') }}: ' + item.returnable"></span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Type: Return / Exchange Toggle --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-extrabold">{{ __('global.pos_return_type') }}</h3>
                    <div class="flex gap-1 bg-slate-100 dark:bg-slate-700 rounded-lg p-0.5">
                        <button @click="isExchange = false; resetExchange()"
                                class="px-3 py-1.5 text-xs font-bold rounded-lg transition"
                                :class="!isExchange ? 'bg-white dark:bg-gray-800 shadow-sm text-brand-primary' : 'text-slate-500 hover:text-slate-700'">
                            {{ __('global.pos_return_type_return') }}
                        </button>
                        <button @click="isExchange = true; initExchangeGrid()"
                                class="px-3 py-1.5 text-xs font-bold rounded-lg transition"
                                :class="isExchange ? 'bg-white dark:bg-gray-800 shadow-sm text-brand-primary' : 'text-slate-500 hover:text-slate-700'">
                            {{ __('global.pos_return_type_exchange') }}
                        </button>
                    </div>
                </div>

                {{-- Exchange: Product Grid with Category Tabs (like POS) --}}
                <div x-show="isExchange" x-cloak class="space-y-3">
                    {{-- Search bar --}}
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" x-model="exchangeSearch" @input.debounce.300ms="searchExchangeProducts()" @keydown.enter.prevent="searchExchangeProducts()"
                                   placeholder="{{ __('global.pos_search_placeholder') }}"
                                   class="w-full border border-slate-200 dark:border-slate-600 rounded-lg pr-10 pl-3 py-2 text-sm focus:ring-2 focus:ring-brand-primary dark:bg-gray-700 dark:text-white dark:border-slate-600">
                        </div>
                        <button @click="searchExchangeProducts()" :disabled="exchangeSearching"
                                class="bg-brand-primary text-white px-3 py-2 rounded-lg hover:bg-brand-hover transition text-sm font-bold disabled:opacity-50">
                            {{ __('global.search') }}
                        </button>
                    </div>

                    {{-- Category Tabs --}}
                    <div class="flex gap-1.5 overflow-x-auto pb-1" x-show="exchangeCategories.length > 0">
                        <template x-for="cat in exchangeCategories" :key="cat.id">
                            <button @click="exchangeFilterCategory = (exchangeFilterCategory === cat.id ? null : cat.id); searchExchangeProducts();"
                                    class="px-2.5 py-1 text-[10px] font-bold rounded-lg border transition-all whitespace-nowrap flex-shrink-0"
                                    :class="exchangeFilterCategory === cat.id ? 'bg-brand-primary text-white border-brand-primary' : 'bg-white dark:bg-gray-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600 hover:border-brand-primary/40'"
                                    x-text="cat.name">
                            </button>
                        </template>
                    </div>

                    {{-- Product Grid --}}
                    <div class="max-h-72 overflow-y-auto">
                        <div x-show="exchangeProducts.length === 0 && exchangeLoading" class="text-center py-6 text-slate-400 text-xs">{{ __('global.pos_loading') }}</div>
                        <div x-show="exchangeProducts.length === 0 && !exchangeLoading" class="text-center py-6 text-slate-400 text-xs">{{ __('global.pos_no_results') }}</div>
                        <div x-show="exchangeProducts.length > 0" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2">
                            <template x-for="p in exchangeProducts" :key="p.id">
                                <div class="bg-white dark:bg-gray-800 rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden cursor-pointer hover:shadow-md hover:-translate-y-0.5 transition-all duration-200"
                                     @click="openExchangeVariantModal(p)">
                                    <div class="aspect-square bg-slate-100 dark:bg-slate-700 overflow-hidden">
                                        <img :src="p.image || '/images/logo.svg'" class="w-full h-full object-cover">
                                    </div>
                                    <div class="p-1.5">
                                        <p class="text-[10px] font-bold truncate leading-tight" x-text="p.name"></p>
                                        <p class="text-[10px] font-black text-brand-primary mt-0.5" x-text="formatPrice(p.current_price || p.base_price)"></p>
                                        <span x-show="p.is_on_sale" class="text-[8px] text-red-600 font-bold">-{{ __('global.pos_sale') }}</span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Selected exchange items --}}
                    <div x-show="exchangeItems.length > 0" class="space-y-1.5 border-t border-slate-200 dark:border-slate-700 pt-3">
                        <p class="text-xs font-bold text-slate-500">{{ __('global.pos_return_exchange_label') }} (<span x-text="exchangeItems.length"></span>)</p>
                        <template x-for="(exItem, idx) in exchangeItems" :key="idx">
                            <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-700/50 rounded-lg p-2 border border-slate-100 dark:border-slate-700">
                                <div class="w-8 h-8 rounded overflow-hidden bg-slate-100 dark:bg-slate-700 flex-shrink-0">
                                    <img :src="exItem.image || '/images/logo.svg'" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold truncate" x-text="exItem.name"></p>
                                    <p class="text-[10px] text-slate-400" x-text="formatPrice(exItem.price) + ' × ' + exItem.quantity"></p>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button @click="exItem.quantity = Math.max(1, exItem.quantity - 1)" class="w-5 h-5 rounded bg-slate-200 dark:bg-slate-600 hover:bg-brand-primary hover:text-white transition text-[10px] font-bold">−</button>
                                    <span class="w-5 text-center text-xs font-black" x-text="exItem.quantity"></span>
                                    <button @click="exItem.quantity = Math.min(99, exItem.quantity + 1)" class="w-5 h-5 rounded bg-slate-200 dark:bg-slate-600 hover:bg-brand-primary hover:text-white transition text-[10px] font-bold">+</button>
                                </div>
                                <button @click="exchangeItems.splice(idx, 1)" class="text-red-400 hover:text-red-600 text-xs font-bold">✕</button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Summary & Confirm --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-slate-200/60 dark:border-slate-700/60 p-4 h-fit sticky top-4">
            <h3 class="font-extrabold text-lg mb-4">{{ __('global.pos_return_summary') }}</h3>

            {{-- Refund Total --}}
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">{{ __('global.pos_return_refund') }}</span>
                    <span class="font-black text-emerald-600" x-text="formatPrice(totalRefund)"></span>
                </div>

                <template x-if="isExchange">
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('global.pos_return_exchange_total') }}</span>
                        <span class="font-black text-brand-primary" x-text="formatPrice(totalExchange)"></span>
                    </div>
                </template>

                <template x-if="isExchange">
                    <div class="border-t border-slate-200 dark:border-slate-700 pt-2 flex justify-between">
                        <span class="font-bold">{{ __('global.pos_return_difference') }}</span>
                        <span class="font-black text-lg" :class="difference >= 0 ? 'text-emerald-600' : 'text-red-600'"
                              x-text="(difference >= 0 ? '' : '− ') + formatPrice(Math.abs(difference))">
                        </span>
                    </div>
                </template>

                <template x-if="isExchange && difference >= 0">
                    <p class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                        {{ __('global.pos_return_customer_gets') }}
                    </p>
                </template>
                <template x-if="isExchange && difference < 0">
                    <p class="text-[10px] text-red-600 font-bold flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                        {{ __('global.pos_return_customer_pays') }}
                    </p>
                </template>
            </div>

            {{-- Payment Method for Refund --}}
            <div class="mt-4">
                <label class="block text-xs font-bold text-slate-500 mb-1.5">{{ __('global.pos_payment') }}</label>
                <div class="grid grid-cols-3 gap-2">
                    <template x-for="method in ['cash', 'card', 'wallet']" :key="method">
                        <button @click="paymentMethod = method"
                                class="py-2 rounded-lg border-2 text-xs font-bold transition"
                                :class="paymentMethod === method ? 'border-brand-primary bg-brand-primary/10 text-brand-primary' : 'border-slate-200 dark:border-slate-600 text-slate-500 hover:border-brand-primary/40'">
                            <span x-text="method === 'cash' ? '{{ __('global.cash') }}' : method === 'card' ? '{{ __('global.card') }}' : '{{ __('global.wallet') }}'"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Confirm Button --}}
            <button @click="processReturn()" :disabled="!canProcess || processing"
                    class="w-full mt-5 py-3 rounded-xl font-extrabold text-sm shadow-lg transition-all duration-300 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="isExchange
                        ? 'bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-500 hover:to-amber-500 text-white'
                        : 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white'">
                <template x-if="processing">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </template>
                <span x-text="processing ? '{{ __('global.pos_return_processing') }}' : (isExchange ? '{{ __('global.pos_return_confirm_exchange') }}' : '{{ __('global.pos_return_confirm') }}')"></span>
            </button>

            {{-- Warning for no returnable items --}}
            <p x-show="selectedOrder && selectedOrder.items.filter(i => i.returnable > 0).length === 0"
               class="mt-3 text-xs text-center text-red-500 font-bold">
                {{ __('global.pos_return_no_returnable') }}
            </p>
        </div>
    </div>

    {{-- Exchange Variant Selection Modal --}}
    <div x-show="showExchangeVariantModal" x-cloak @keydown.escape.window="closeExchangeVariantModal()" @click="closeExchangeVariantModal()" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="animation: fadeIn 0.15s ease-out;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full p-6 max-h-[85vh] overflow-y-auto" @click.stop>
            <div class="flex items-start gap-4 mb-5">
                <div class="w-16 h-16 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-700 flex-shrink-0">
                    <img :src="exchangeSelectedProduct?.image || '/images/logo.svg'" class="w-full h-full object-cover">
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-lg leading-tight" x-text="exchangeSelectedProduct?.name"></h3>
                    <div class="flex items-center gap-2 mt-1">
                        <template x-if="exchangeSelectedProduct?.is_on_sale">
                            <span class="text-sm font-black text-red-600 dark:text-red-400" x-text="formatPrice(exchangeSelectedProduct.current_price)"></span>
                        </template>
                        <span class="text-sm font-black text-brand-primary dark:text-accent" :class="exchangeSelectedProduct?.is_on_sale ? 'text-xs line-through text-slate-400' : ''" x-text="formatPrice(exchangeSelectedProduct?.base_price)"></span>
                    </div>
                </div>
                <button @click="closeExchangeVariantModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <template x-if="exchangeSelectedProduct?.has_variants">
                <div>
                    <div class="mb-4" x-show="exchangeUniqueColors.length > 0">
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">{{ __('global.admin_color') }}</label>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="v in exchangeUniqueColors" :key="v">
                                <button @click="exchangeSelectedColor = v"
                                        class="px-3 py-1.5 text-xs font-bold rounded-lg border-2 transition-all"
                                        :class="exchangeSelectedColor === v ? 'border-brand-primary bg-brand-primary/10 text-brand-primary dark:border-accent dark:bg-accent/20 dark:text-accent' : 'border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-brand-primary/40'"
                                        x-text="v"></button>
                            </template>
                        </div>
                    </div>
                    <div class="mb-4" x-show="exchangeUniqueSizes.length > 0">
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">{{ __('global.admin_size') }}</label>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="v in exchangeUniqueSizes" :key="v">
                                <button @click="exchangeSelectedSize = v"
                                        class="px-3 py-1.5 text-xs font-bold rounded-lg border-2 transition-all"
                                        :class="exchangeSelectedSize === v ? 'border-brand-primary bg-brand-primary/10 text-brand-primary dark:border-accent dark:bg-accent/20 dark:text-accent' : 'border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:border-brand-primary/40'"
                                        x-text="v"></button>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="!exchangeSelectedProduct?.has_variants">
                <div class="mb-4 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('global.pos_simple_product') }}</p>
                </div>
            </template>

            <div class="mb-4 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ __('global.pos_quantity') }}</label>
                    <span class="text-[10px] text-slate-500" x-show="exchangeSelectedVariant">
                        {{ __('global.pos_stock_available') }}: <span class="font-bold" x-text="exchangeSelectedVariant?.stock || 0"></span>
                    </span>
                </div>
                <div class="flex items-center gap-3 mt-2">
                    <button @click="exchangeQty = Math.max(1, exchangeQty - 1)" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 hover:bg-brand-primary hover:text-white transition flex items-center justify-center font-bold text-lg">−</button>
                    <input type="number" x-model="exchangeQty" @input="exchangeQty = Math.max(1, Math.min(exchangeMaxStock, parseInt($event.target.value) || 1))" class="w-16 text-center text-xl font-black bg-transparent border-0 p-0 focus:outline-none focus:ring-0 [&::-webkit-inner-spin-button]:appearance-none">
                    <button @click="exchangeQty = Math.min(exchangeMaxStock, exchangeQty + 1)" class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700 hover:bg-brand-primary hover:text-white transition flex items-center justify-center font-bold text-lg">+</button>
                </div>
            </div>

            <div class="flex gap-3">
                <button @click="closeExchangeVariantModal()" class="flex-1 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                    {{ __('global.cancel') }}
                </button>
                <button @click="confirmExchangeAdd()" :disabled="!exchangeCanAdd"
                        class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-orange-600 to-amber-600 text-white text-sm font-extrabold hover:from-orange-500 hover:to-amber-500 transition shadow-lg disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>{{ __('global.pos_return_exchange_add') }}</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Success Modal --}}
    <div x-show="result" x-cloak @keydown.escape.window="result = null" @click="result = null"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-6 text-center" @click.stop>
            <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
                 :class="result?.type === 'exchange' ? 'bg-orange-100 dark:bg-orange-900/50' : 'bg-emerald-100 dark:bg-emerald-900/50'">
                <svg class="w-8 h-8" :class="result?.type === 'exchange' ? 'text-orange-600' : 'text-emerald-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="font-extrabold text-lg mb-2" x-text="result?.message"></h3>
            <div class="text-sm space-y-1 text-slate-500 mb-4">
                <p x-show="result?.refund_amount > 0">
                    {{ __('global.pos_return_refund') }}: <span class="font-black text-emerald-600" x-text="formatPrice(result?.refund_amount)"></span>
                </p>
                <p x-show="result?.exchange_total > 0">
                    {{ __('global.pos_return_exchange_total') }}: <span class="font-black text-brand-primary" x-text="formatPrice(result?.exchange_total)"></span>
                </p>
                <p x-show="result?.exchange_total > 0 && result?.difference !== 0">
                    {{ __('global.pos_return_difference') }}: <span class="font-black" :class="result?.difference >= 0 ? 'text-emerald-600' : 'text-red-600'" x-text="formatPrice(Math.abs(result?.difference))"></span>
                </p>
            </div>
            <button @click="resetAll()"
                    class="w-full py-2.5 rounded-xl bg-brand-primary text-white font-extrabold hover:bg-brand-hover transition shadow-lg">
                {{ __('global.close') }}
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('posReturnApp', () => ({
        searchQuery: '',
        searching: false,
        searched: false,
        orders: [],
        selectedOrder: null,

        returnItems: {},
        isExchange: false,

        exchangeSearch: '',
        exchangeSearching: false,
        exchangeLoading: false,
        exchangeProducts: [],
        exchangeCategories: [],
        exchangeFilterCategory: null,
        exchangeItems: [],

        showExchangeVariantModal: false,
        exchangeSelectedProduct: null,
        exchangeSelectedColor: null,
        exchangeSelectedSize: null,
        exchangeQty: 1,
        exchangeMaxStock: 999,

        paymentMethod: 'cash',
        processing: false,
        result: null,

        init() {
            this.$nextTick(() => this.$refs?.searchInput?.focus());
        },

        async searchOrders() {
            if (!this.searchQuery.trim()) return;
            this.searching = true;
            this.searched = true;
            this.selectedOrder = null;
            this.orders = [];
            this.resetReturn();

            try {
                const res = await fetch('{{ route('admin.pos.return.search') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ q: this.searchQuery.trim() })
                });
                this.orders = await res.json();
            } catch (e) {
                this.orders = [];
            } finally {
                this.searching = false;
            }
        },

        selectOrder(order) {
            this.selectedOrder = order;
            this.resetReturn();
        },

        resetReturn() {
            this.returnItems = {};
            this.isExchange = false;
            this.exchangeItems = [];
            this.exchangeProducts = [];
            this.exchangeCategories = [];
            this.exchangeFilterCategory = null;
            this.exchangeSearch = '';
            this.exchangeLoading = false;
            this.showExchangeVariantModal = false;
            this.exchangeSelectedProduct = null;
            this.paymentMethod = 'cash';
            this.result = null;
        },

        resetExchange() {
            this.exchangeItems = [];
            this.exchangeProducts = [];
            this.exchangeSearch = '';
            this.exchangeFilterCategory = null;
            this.showExchangeVariantModal = false;
            this.exchangeSelectedProduct = null;
        },

        async initExchangeGrid() {
            this.exchangeLoading = true;
            this.exchangeProducts = [];
            this.exchangeSearch = '';
            this.exchangeFilterCategory = null;
            try {
                const [catRes, prodRes] = await Promise.all([
                    fetch('{{ route('admin.pos.categories') }}'),
                    fetch('{{ route('admin.pos.search') }}?limit=30')
                ]);
                this.exchangeCategories = await catRes.json();
                this.exchangeProducts = await prodRes.json();
            } catch (e) {}
            finally { this.exchangeLoading = false; }
        },

        async searchExchangeProducts() {
            this.exchangeLoading = true;
            try {
                let url = `{{ route('admin.pos.search') }}?search=${encodeURIComponent(this.exchangeSearch)}&limit=30`;
                if (this.exchangeFilterCategory) url += `&category_id=${this.exchangeFilterCategory}`;
                const res = await fetch(url);
                this.exchangeProducts = await res.json();
            } catch (e) {
                this.exchangeProducts = [];
            } finally {
                this.exchangeLoading = false;
            }
        },

        toggleReturnItem(itemId, checked) {
            if (checked) {
                const item = this.selectedOrder.items.find(i => i.id === itemId);
                this.returnItems[itemId] = Math.min(item.returnable, 1);
            } else {
                delete this.returnItems[itemId];
            }
        },

        toggleAllReturnItems(checked) {
            if (checked) {
                this.selectedOrder.items.forEach(item => {
                    if (item.returnable > 0) {
                        this.returnItems[item.id] = Math.min(item.returnable, 1);
                    }
                });
            } else {
                this.returnItems = {};
            }
        },

        addExchangeItem(variant) {
            const existing = this.exchangeItems.find(e => e.variant_id === variant.variant_id);
            if (existing) {
                existing.quantity += variant.quantity;
            } else {
                this.exchangeItems.push({ ...variant });
            }
        },

        openExchangeVariantModal(product) {
            this.exchangeSelectedProduct = product;
            this.exchangeSelectedColor = null;
            this.exchangeSelectedSize = null;
            this.exchangeQty = 1;
            this.exchangeMaxStock = 999;

            if (!product.has_variants && product.variants.length > 0) {
                this.exchangeSelectedColor = product.variants[0].color;
                this.exchangeSelectedSize = product.variants[0].size;
                this.exchangeMaxStock = product.variants[0].stock;
            } else if (product.has_variants) {
                this.exchangeMaxStock = Math.max(...product.variants.map(v => v.stock), 0);
            }

            this.showExchangeVariantModal = true;
        },

        closeExchangeVariantModal() {
            this.showExchangeVariantModal = false;
            this.exchangeSelectedProduct = null;
            this.exchangeSelectedColor = null;
            this.exchangeSelectedSize = null;
            this.exchangeQty = 1;
        },

        get exchangeUniqueColors() {
            if (!this.exchangeSelectedProduct) return [];
            return [...new Set(this.exchangeSelectedProduct.variants.map(v => v.color).filter(Boolean))];
        },

        get exchangeUniqueSizes() {
            if (!this.exchangeSelectedProduct) return [];
            return [...new Set(this.exchangeSelectedProduct.variants.map(v => v.size).filter(Boolean))];
        },

        get exchangeSelectedVariant() {
            if (!this.exchangeSelectedProduct) return null;
            return this.exchangeSelectedProduct.variants.find(v =>
                (!this.exchangeSelectedColor || v.color === this.exchangeSelectedColor) &&
                (!this.exchangeSelectedSize || v.size === this.exchangeSelectedSize)
            );
        },

        get exchangeCanAdd() {
            if (!this.exchangeSelectedProduct) return false;
            if (this.exchangeSelectedProduct.has_variants) {
                return this.exchangeSelectedVariant !== null;
            }
            return this.exchangeSelectedProduct.variants.length > 0;
        },

        confirmExchangeAdd() {
            if (!this.exchangeCanAdd) return;
            const variant = this.exchangeSelectedVariant || this.exchangeSelectedProduct.variants[0];
            if (!variant) return;

            this.addExchangeItem({
                variant_id: variant.id,
                name: this.exchangeSelectedProduct.name,
                price: variant.price,
                quantity: this.exchangeQty,
                image: this.exchangeSelectedProduct.image,
            });

            this.closeExchangeVariantModal();
        },

        get totalRefund() {
            if (!this.selectedOrder) return 0;
            return Object.entries(this.returnItems).reduce((sum, [itemId, qty]) => {
                const item = this.selectedOrder.items.find(i => i.id === parseInt(itemId));
                return sum + (item ? item.unit_price * qty : 0);
            }, 0);
        },

        get totalExchange() {
            return this.exchangeItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        get difference() {
            return this.totalRefund - this.totalExchange;
        },

        get canProcess() {
            const hasReturnItems = Object.values(this.returnItems).some(q => q > 0);
            if (!hasReturnItems) return false;
            if (this.isExchange && this.exchangeItems.length === 0) return false;
            return true;
        },

        async processReturn() {
            if (!this.canProcess || this.processing) return;
            this.processing = true;

            const items = Object.entries(this.returnItems)
                .filter(([_, qty]) => qty > 0)
                .map(([orderItemId, qty]) => ({
                    order_item_id: parseInt(orderItemId),
                    quantity: qty,
                }));

            const payload = {
                order_id: this.selectedOrder.id,
                type: this.isExchange ? 'exchange' : 'return',
                items,
                payment_method: this.paymentMethod,
            };

            if (this.isExchange && this.exchangeItems.length > 0) {
                payload.exchange_items = this.exchangeItems.map(e => ({
                    variant_id: e.variant_id,
                    quantity: e.quantity,
                }));
            }

            try {
                const res = await fetch('{{ route('admin.pos.return.process') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!res.ok) {
                    const msg = data.error || data.message || '{{ __('global.pos_error') }}';
                    alert(msg);
                    return;
                }
                this.result = data;
            } catch (e) {
                alert('{{ __('global.pos_error') }}');
            } finally {
                this.processing = false;
            }
        },

        resetAll() {
            this.result = null;
            this.selectedOrder = null;
            this.orders = [];
            this.searched = false;
            this.searchQuery = '';
            this.resetReturn();
            this.$nextTick(() => this.$refs?.searchInput?.focus());
        },

        formatPrice(price) {
            const value = Math.round(parseFloat(price || 0));
            return new Intl.NumberFormat('{{ app()->getLocale() === "ar" ? "ar-EG" : "en-EG" }}', {
                style: 'currency', currency: 'EGP', maximumFractionDigits: 0
            }).format(value);
        },
    }));
});
</script>
@endpush
