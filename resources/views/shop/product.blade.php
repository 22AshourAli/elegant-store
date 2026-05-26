@extends('layouts.store')

@section('seo')
    {!! SEO::generate() !!}
@endsection

@section('content')
<div class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700 py-4 mb-8">
    <div class="container mx-auto px-4">
        <nav class="flex text-sm text-gray-500" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 space-x-reverse md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ __('global.home') }}</a>
                </li>
                @if($product->category)
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mx-1 {{ app()->getLocale() === 'ar' ? 'transform rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('shop.category', $product->category->slug) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">{{ $product->category->name }}</a>
                    </div>
                </li>
                @endif
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mx-1 {{ app()->getLocale() === 'ar' ? 'transform rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="text-gray-800 dark:text-gray-200 font-semibold truncate max-w-xs">{{ $product->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>

<script>
    window.productData = {
        product: @json($product),
        colors: @json($colors),
        sizes: @json($sizes),
        colorImages: @json($colorImages)
    };
</script>
<div class="container mx-auto px-4 mb-20" x-data="productView(window.productData.product, window.productData.colors, window.productData.sizes, window.productData.colorImages)">
    <div class="grid md:grid-cols-2 gap-10 lg:gap-16">

        <!-- Image Gallery (Left Side) -->
        <div class="space-y-4">
            <!-- Main Image with Alpine Binding -->
            <div class="relative bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm border border-gray-100 dark:border-gray-700 aspect-[4/5] md:aspect-square group">
                <img :src="currentImage" alt="{{ $product->name }}" class="w-full h-full object-cover transition-opacity duration-500 ease-in-out" x-ref="mainImg">

                @if($product->isOnSale)
                <div class="absolute top-4 right-4 bg-red-500 text-white font-bold px-3 py-1 rounded-full shadow-lg">
                    {{ __('global.on_sale') }}
                </div>
                @endif
            </div>

            <!-- Thumbnails (If multiple images exist) -->
            @if($product->getMedia('product_images')->count() > 1)
            <div class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide">
                @foreach($product->getMedia('product_images') as $media)
                <button @click="currentImage = '{{ $media->getUrl('responsive') }}'" class="w-20 h-24 flex-shrink-0 rounded-lg overflow-hidden border-2 focus:outline-none transition-all" :class="currentImage === '{{ $media->getUrl('responsive') }}' ? 'border-indigo-600 opacity-100' : 'border-transparent opacity-60 hover:opacity-100'">
                    <img src="{{ $media->getUrl('thumb') }}" class="w-full h-full object-cover">
                </button>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Product Details (Right Side) -->
        <div class="flex flex-col text-start">
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white mb-2">{{ $product->name }}</h1>

            <!-- Rating placeholder -->
            <div class="flex items-center gap-2 mb-6">
                <div class="flex text-yellow-400">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <svg class="w-5 h-5 text-gray-300 dark:text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                </div>
                <span class="text-sm text-gray-500">(12 {{ __('global.rating_label') }})</span>
            </div>

            <!-- Price & Countdown -->
            <div class="flex flex-col mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-baseline gap-4">
                    <span x-text="formatPrice(currentPrice)" class="text-3xl font-extrabold text-indigo-600 dark:text-indigo-400"></span>
                    <span x-show="originalPrice !== currentPrice" x-cloak x-text="formatPrice(originalPrice)" class="text-lg text-gray-400 line-through"></span>
                </div>
                @if($product->discount_end)
                    @php
                        $daysLeft = now()->diffInDays($product->discount_end, false);
                    @endphp
                    @if($daysLeft >= 0)
                        <div class="mt-3 text-xs text-red-500 dark:text-red-400 font-bold flex items-center gap-1.5 bg-red-50 dark:bg-red-950/20 px-3 py-2 rounded-lg w-fit">
                            <svg class="w-4.5 h-4.5 animate-pulse text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ __('global.limited_time_offer', ['days' => $daysLeft]) }}</span>
                        </div>
                    @endif
                @endif
            </div>

            @if(count($colors) > 0)
            <!-- Color Selection -->
            <div class="mb-6">
                <div class="flex justify-between items-center mb-2">
                    <label class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('global.color') }}:</label>
                    <span class="text-sm text-gray-500 font-medium" x-text="selectedColor"></span>
                </div>
                <div class="flex flex-wrap gap-3">
                    <template x-for="color in colors" :key="color">
                        <button @click="selectColor(color)"
                                class="relative w-12 h-12 rounded-full focus:outline-none transition-all duration-200"
                                :class="selectedColor === color ? 'ring-2 ring-offset-2 ring-indigo-600 scale-110 shadow-md dark:ring-offset-gray-900' : 'ring-1 ring-gray-200 dark:ring-gray-700 hover:scale-105'"
                                :title="color">
                            <span class="block w-full h-full rounded-full border border-black/10 overflow-hidden">
                                <img x-show="colorImages[normalize(color)]" :src="colorImages[normalize(color)]" class="w-full h-full object-cover">
                                <span x-show="!colorImages[normalize(color)]" class="flex items-center justify-center w-full h-full bg-gray-100 text-xs text-gray-500 font-bold" x-text="color.substring(0,2)"></span>
                            </span>

                            <!-- Checkmark for selected -->
                            <span x-show="selectedColor === color" class="absolute -bottom-1 -right-1 bg-indigo-600 text-white rounded-full p-0.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </span>
                        </button>
                    </template>
                </div>
            </div>
            @endif

            @if(count($sizes) > 0)
            <!-- Size Selection -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <label class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('global.size') }}:</label>
                </div>
                <div class="grid grid-cols-4 md:grid-cols-5 gap-3">
                    <template x-for="size in sizes" :key="size">
                        <button @click="selectedSize = size"
                                class="py-3 px-2 border rounded-lg text-sm font-bold focus:outline-none transition-all duration-200"
                                :class="selectedSize === size ? 'border-indigo-600 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 dark:border-indigo-500 shadow-sm' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800'">
                            <span x-text="size"></span>
                        </button>
                    </template>
                </div>
            </div>
            @endif

            <!-- Add to Cart Actions -->
            <div class="bg-gray-50 dark:bg-gray-800/50 p-6 rounded-xl border border-gray-100 dark:border-gray-700 mb-8">

                <!-- Stock Status -->
                <div class="flex items-center gap-2 mb-4">
                    <div x-show="stockStatus === 'in_stock'" x-cloak class="flex items-center text-green-600 dark:text-green-400">
                        <span class="relative flex h-3 w-3 ml-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </span>
                        <span class="font-bold text-sm">{{ __('global.in_stock') }} (<span x-text="availableQty"></span>)</span>
                    </div>

                    <div x-show="stockStatus === 'out_of_stock'" x-cloak class="flex items-center text-red-600 dark:text-red-400">
                        <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="font-bold text-sm">{{ __('global.out_of_stock') }}</span>
                    </div>

                    <div x-show="stockStatus === 'select_options'" x-cloak class="flex items-center text-yellow-600 dark:text-yellow-400">
                        <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="font-bold text-sm">{{ __('global.select_options') }}</span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Quantity -->
                    <div class="flex items-center border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 justify-between">
                        <button @click="if(qty > 1) qty--" class="px-4 py-3 text-gray-500 hover:text-indigo-600 focus:outline-none transition-colors">-</button>
                        <input type="number" x-model.number="qty" min="1" :max="availableQty" class="w-12 text-center bg-transparent border-0 focus:ring-0 p-0 font-bold text-gray-900 dark:text-gray-100" readonly>
                        <button @click="if(qty < availableQty) qty++" class="px-4 py-3 text-gray-500 hover:text-indigo-600 focus:outline-none transition-colors">+</button>
                    </div>

                    <!-- Add Button -->
                    <button @click="addToCart"
                            :disabled="stockStatus !== 'in_stock'"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 transform active:scale-95 shadow-md flex justify-center items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span>{{ __('global.add_to_cart') }}</span>
                    </button>

                    <!-- Buy Now Button -->
                    <button @click="buyNow"
                            :disabled="stockStatus !== 'in_stock'"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 transform active:scale-95 shadow-md flex justify-center items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        <span>{{ __('global.buy_now') }}</span>
                    </button>
                </div>
            </div>

            <!-- Description -->
            <div class="mt-4">
                <h3 class="font-bold text-lg mb-3">{{ __('global.description') }}</h3>
                <div class="text-gray-600 dark:text-gray-300 leading-relaxed text-sm">
                    {!! nl2br($product->description) !!}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productView', (product, colors, sizes, colorImages) => ({
            colors: colors,
            sizes: sizes,
            selectedColor: colors.length > 0 ? colors[0] : '',
            selectedColorKey: colors.length > 0 ? (typeof colors[0] === 'string' ? colors[0].toLowerCase().trim() : colors[0]) : '',
            selectedSize: sizes.length > 0 ? sizes[0] : '',
            colorImages: colorImages,
            product: product,
            qty: 1,
            normalize(v) { return typeof v === 'string' ? v.toLowerCase().trim() : v; },

            init() {
                this.selectedColorKey = this.normalize(this.selectedColor);
                if (this.colorImages[this.selectedColorKey]) {
                    this.currentImage = this.colorImages[this.selectedColorKey];
                }

                this.$watch('selectedColor', value => {
                    this.qty = 1; // Reset qty when option changes
                    this.selectedColorKey = this.normalize(value);
                    if (this.colorImages[this.selectedColorKey]) {
                        this.currentImage = this.colorImages[this.selectedColorKey];
                    }
                });
                this.$watch('selectedSize', value => {
                    this.qty = 1;
                });
            },

            get currentVariant() {
                if(!product.has_variants || product.has_variants == 0) return product.variants[0]; // Default variant
                if(!this.selectedColor || !this.selectedSize) return null;
                return product.variants.find(v => this.normalize(v.color) === this.normalize(this.selectedColor) && this.normalize(v.size) === this.normalize(this.selectedSize));
            },

            get currentPrice() {
                if((!product.has_variants || product.has_variants == 0) && product.variants[0]) {
                    // It's a simple product, check default variant or product price
                    let v = product.variants[0];
                    let base = v.price_override ?? product.base_price;
                    if(v.sale_price) return parseFloat(v.sale_price);
                    if(product.is_on_sale) return parseFloat(product.current_price);
                    return parseFloat(base);
                }
                return this.currentVariant ? parseFloat(this.currentVariant.current_price) : parseFloat(product.current_price);
            },

            get originalPrice() {
                if((!product.has_variants || product.has_variants == 0) && product.variants[0]) {
                    return parseFloat(product.variants[0].price_override ?? product.base_price);
                }
                return this.currentVariant ? parseFloat(this.currentVariant.price_override ?? product.base_price) : parseFloat(product.base_price);
            },

            currentImage: '{{ $product->getFirstMediaUrl("product_images") ?: "/images/placeholder.jpg" }}',

            get availableQty() {
                if (!this.currentVariant) return 0;
                // Currently fetching from branch 1 (Default Branch)
                let branchStock = this.currentVariant.branches.find(b => b.id === 1);
                return branchStock ? parseInt(branchStock.pivot.stock) : 0;
            },

            get stockStatus() {
                if((product.has_variants == 1 || product.has_variants == true) && (!this.selectedColor || !this.selectedSize)) {
                    return 'select_options';
                }
                return this.availableQty > 0 ? 'in_stock' : 'out_of_stock';
            },

            selectColor(color) {
                this.selectedColor = color;
            },

            formatPrice(price) {
                const locale = '{{ app()->getLocale() }}' === 'ar' ? 'ar-EG' : 'en-EG';
                // Round to integer for clean display, avoid excessive decimals
                const value = Math.round(parseFloat(price || 0));
                return new Intl.NumberFormat(locale, { style: 'currency', currency: 'EGP', maximumFractionDigits: 0 }).format(value);
            },

            addToCart() {
                if (!this.currentVariant) return;
                fetch(`/cart/add/${this.currentVariant.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        quantity: this.qty
                    })
                })
                .then(res => res.json())
                .then(data => {
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.cartCount } }));
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message, type: 'success' } }));
                })
                .catch(err => {
                    console.error(err);
                    const errMsg = '{{ __('global.error_adding_to_cart') }}';
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: errMsg, type: 'error' } }));
                });
            },

            buyNow() {
                if (!this.currentVariant) return;
                fetch(`/buy-now/${this.currentVariant.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ quantity: this.qty })
                })
                .then(() => {
                    window.location.href = '{{ route('checkout') }}';
                })
                .catch(() => {
                    window.location.href = '{{ route('checkout') }}';
                });
            }
        }));
    });
</script>

@push('seo')
  @php
    $productSchema = [
      '@context' => 'https://schema.org',
      '@type' => 'Product',
      'name' => $product->name,
      'description' => $product->meta_description ?? Str::limit(strip_tags($product->description), 160),
      'image' => $product->getFirstMediaUrl('product_images') ?: asset('images/placeholder.jpg'),
      'offers' => [
        '@type' => 'Offer',
        'price' => $product->current_price ?? $product->base_price,
        'priceCurrency' => 'EGP',
        'availability' => ($product->variants->first()?->branches->sum('pivot.stock') ?? 0) > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
      ]
    ];
  @endphp
  {!! '<script type="application/ld+json">' . json_encode($productSchema) . '</script>' !!}
@endpush
@endsection
