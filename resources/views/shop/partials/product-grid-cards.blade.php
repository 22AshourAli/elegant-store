@if($products->count() > 0)
    <div class="product-grid stagger in">
        @foreach($products as $product)
            <div class="product-card-wrapper">
                @include('shop.partials.product-card', ['product' => $product, 'wishlistIds' => $wishlistIds ?? [], 'cartProductIds' => $cartProductIds ?? []])
            </div>
        @endforeach
    </div>
@endif
