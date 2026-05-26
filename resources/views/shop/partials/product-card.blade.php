<div class="product-card border border-gray-100 dark:border-gray-700">
    <a href="{{ route('shop.product', $product->slug) }}" class="block relative overflow-hidden">
        @php
            $image = $product->getFirstMediaUrl('product_images', 'thumb') ?: ($product->getFirstMediaUrl('product_images') ?: asset('images/placeholder.jpg'));
        @endphp
        <img src="{{ $image }}" alt="{{ $product->name }}" loading="lazy" class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-500">
        @if($product->isOnSale)
            <div class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded shadow">
                {{ __('global.on_sale') }}
            </div>
        @endif
    </a>
    <div class="p-4">
        <h3 class="text-lg font-semibold mb-2">
            <a href="{{ route('shop.product', $product->slug) }}" class="hover:text-indigo-500 dark:hover:text-indigo-400 line-clamp-1">
                {{ $product->name }}
            </a>
        </h3>
        <div class="flex items-center justify-between mt-4">
            <div class="flex flex-col">
                @if($product->isOnSale)
                    <span class="text-gray-400 text-sm line-through">{{ (int) round($product->base_price) }} {{ __('global.currency') }}</span>
                    <span class="text-red-500 font-bold text-lg">{{ (int) round($product->current_price) }} {{ __('global.currency') }}</span>
                @else
                    <span class="text-indigo-600 dark:text-indigo-400 font-bold text-lg">{{ (int) round($product->current_price) }} {{ __('global.currency') }}</span>
                @endif
            </div>
            <a href="{{ route('shop.product', $product->slug) }}" class="bg-indigo-600 text-white p-2.5 rounded-full hover:bg-indigo-700 hover:scale-110 transition-all shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" title="{{ __('global.view_details') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            </a>
        </div>
    </div>
</div>
