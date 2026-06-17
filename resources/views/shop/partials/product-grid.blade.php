@if($products->count() > 0)
    <div class="product-grid stagger in">
        @foreach($products as $product)
            <div class="product-card-wrapper">
                @include('shop.partials.product-card', ['product' => $product, 'wishlistIds' => $wishlistIds ?? [], 'cartProductIds' => $cartProductIds ?? []])
            </div>
        @endforeach
    </div>
    <div class="mt-8 flex items-center justify-center">
        {{ $products->onEachSide(1)->links('pagination.tailwind') }}
    </div>
@else
    <div class="text-center text-slate-500 py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800">
        {{ __('global.filter_no_results') }}
    </div>
@endif
