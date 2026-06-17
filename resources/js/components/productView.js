export default function productView(data) {
    const { product, colors, sizes, colorImages, firstImageUrl } = data;

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
        },

        formatPrice(price) {
            const value = Math.round(parseFloat(price || 0));
            return value.toLocaleString() + ' EGP';
        },

        formatNumber(value) {
            return Math.round(parseFloat(value || 0)).toLocaleString() + ' EGP';
        },

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
            .then(data => {
                this.cartLoading = false;
                window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.cartCount } }));
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message, type: 'success' } }));
            })
            .catch(() => {
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
            .catch(() => { window.location.href = '/checkout'; });
        }
    };
}

function normalize(v) {
    return typeof v === 'string' ? v.toLowerCase().trim() : v;
}
