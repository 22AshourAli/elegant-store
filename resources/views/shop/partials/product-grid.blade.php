@if($products->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
        @foreach($products as $product)
            <div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">
                <div x-show="!loaded">
                    @include('components.skeleton-product')
                </div>
                <div x-show="loaded" x-transition.opacity.duration.500 style="display: none;">
                    @include('shop.partials.product-card', ['product' => $product])
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-8">
        {{ $products->links() }}
    </div>
@else
    <div class="text-center text-gray-500 py-10">
        {{ __('global.filter_no_results') }}
    </div>
@endif
