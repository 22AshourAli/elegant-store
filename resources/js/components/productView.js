import { formatPrice } from '../app';

export default function productView(productViewData) {
    const { product, colors, sizes, colorImages, firstImageUrl } = productViewData;

    return {
        cartLoading: false,
        buyLoading: false,
        colors,
        sizes,
        selectedColor: colors.length > 0 ? colors[0] : '',
        selectedColorKey: colors.length > 0 ? normalize(colors[0]) : '',
        selectedSize: sizes.length > 0 ? sizes[0] : '',
        colorImages,
        product,
        firstImageUrl: firstImageUrl || '',
        qty: 1,
        currentImage: firstImageUrl || (product.all_images?.[0]) || '',

        init() {
            this.selectedColorKey = normalize(this.selectedColor);
            if (this.colorImages[this.selectedColorKey]) {
                this.currentImage = this.colorImages[this.selectedColorKey];
            }

            this.$watch('selectedColor', value => {
                this.qty = 1;
                this.selectedColorKey = normalize(value);
                if (this.colorImages[this.selectedColorKey]) {
                    this.currentImage = this.colorImages[this.selectedColorKey];
                } else {
                    this.currentImage = this.firstImageUrl || (product.all_images?.[0]) || '';
                }
            });

            this.$watch('selectedSize', () => { this.qty = 1; });
        },

        get currentVariant() {
            if (!product.has_variants || product.has_variants == 0) return product.variants[0];
            if (!this.selectedColor || !this.selectedSize) return null;
            return product.variants.find(v =>
                normalize(v.color) === normalize(this.selectedColor) &&
                normalize(v.size) === normalize(this.selectedSize)
            );
        },

        get currentPrice() {
            if ((!product.has_variants || product.has_variants == 0) && product.variants[0]) {
                let v = product.variants[0];
                let base = v.price_override ?? product.base_price;
                return product.is_on_sale ? parseFloat(product.current_price) : parseFloat(base);
            }
            return this.currentVariant
                ? parseFloat(this.currentVariant.current_price)
                : parseFloat(product.current_price);
        },

        get originalPrice() {
            if ((!product.has_variants || product.has_variants == 0) && product.variants[0]) {
                return parseFloat(product.variants[0].price_override ?? product.base_price);
            }
            return this.currentVariant
                ? parseFloat(this.currentVariant.price_override ?? product.base_price)
                : parseFloat(product.base_price);
        },

        get availableQty() {
            if (!this.currentVariant) return 0;
            return this.currentVariant.total_stock ? parseInt(this.currentVariant.total_stock) : 0;
        },

        get stockStatus() {
            if ((product.has_variants == 1 || product.has_variants == true) && (!this.selectedColor || !this.selectedSize)) {
                return 'select_options';
            }
            return this.availableQty > 0 ? 'in_stock' : 'out_of_stock';
        },

        selectColor(color) {
            this.selectedColor = color;
            const el = document.querySelector('.product-main-image');
            if (el && window.innerWidth < 768) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        },

        formatPrice,
        formatNumber: formatPrice,
        normalize,

        addToCart() {
            if (!this.currentVariant) return;
            this.cartLoading = true;
            fetch(`/cart/add/${this.currentVariant.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? ''
                },
                body: JSON.stringify({ quantity: this.qty })
            })
            .then(res => res.json())
            .then(responseData => {
                this.cartLoading = false;
                window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: responseData.cartCount } }));
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: responseData.message, type: 'success' } }));
            })
            .catch(e => {
                console.error('Add to cart failed:', e);
                this.cartLoading = false;
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Error adding to cart', type: 'error' } }));
            });
        },

        buyNow() {
            if (!this.currentVariant) return;
            this.buyLoading = true;
            fetch(`/buy-now/${this.currentVariant.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? ''
                },
                body: JSON.stringify({ quantity: this.qty })
            })
            .then(() => { window.location.href = '/checkout'; })
            .catch(e => {
                console.error('Buy now failed:', e);
                window.location.href = '/checkout';
            });
        }
    };
}

function normalize(v) {
    return typeof v === 'string' ? v.toLowerCase().trim() : v;
}
