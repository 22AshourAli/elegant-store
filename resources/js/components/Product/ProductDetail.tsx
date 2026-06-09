import { useState, useEffect, useMemo } from 'react';
import { fetchProductDetail } from '../../api/productApi';
import ProductGallery from './ProductGallery';
import type { ProductDetail as ProductDetailType } from '../../types/product';

interface Props {
  slug: string;
  onAddToCart: (variantId: number, quantity: number) => void;
}

export default function ProductDetail({ slug, onAddToCart }: Props) {
  const [product, setProduct] = useState<ProductDetailType | null>(null);
  const [loading, setLoading] = useState(true);
  const [selectedColor, setSelectedColor] = useState<string | null>(null);
  const [selectedSize, setSelectedSize] = useState<string | null>(null);
  const [quantity, setQuantity] = useState(1);

  useEffect(() => {
    setLoading(true);
    fetchProductDetail(slug)
      .then((res) => {
        const p = res.data ?? res;
        setProduct(p);
        if (p.colors.length > 0) setSelectedColor(p.colors[0]);
        if (p.sizes.length > 0) setSelectedSize(p.sizes[0]);
      })
      .finally(() => setLoading(false));
  }, [slug]);

  const selectedVariant = useMemo(() => {
    if (!product) return null;
    return product.variants.find(
      (v) =>
        (!selectedColor || v.color === selectedColor) &&
        (!selectedSize || v.size === selectedSize)
    );
  }, [product, selectedColor, selectedSize]);

  const activePrice = selectedVariant?.price ?? product?.current_price ?? 0;

  // Filter available sizes by selected color
  const availableSizes = useMemo(() => {
    if (!product || !selectedColor) return product?.sizes ?? [];
    return product.variants
      .filter((v) => v.color === selectedColor && v.stock > 0)
      .map((v) => v.size)
      .filter(Boolean)
      .filter((v, i, a) => a.indexOf(v) === i);
  }, [product, selectedColor]);

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[60vh]">
        <div className="w-10 h-10 border-4 border-gray-200 border-t-black rounded-full animate-spin" />
      </div>
    );
  }

  if (!product) {
    return (
      <div className="text-center py-20 text-gray-500">Product not found</div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto px-4 py-8 grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
      {/* Gallery */}
      <ProductGallery
        defaultImage={product.default_image}
        allImages={product.all_images}
        colorGalleries={product.color_galleries}
        colors={product.colors}
        selectedColor={selectedColor}
        onColorSelect={(c) => {
          setSelectedColor(c);
          setSelectedSize(null);
        }}
      />

      {/* Info */}
      <div className="flex flex-col gap-6">
        <div>
          {product.is_on_sale && (
            <span className="text-xs font-bold text-red-600 bg-red-50 dark:bg-red-900/30 px-2 py-1 rounded-full">
              SALE
            </span>
          )}
          <h1 className="text-2xl lg:text-3xl font-bold mt-2">{product.name}</h1>
          {product.category && (
            <p className="text-sm text-gray-500 mt-1">{product.category.name}</p>
          )}
        </div>

        {/* Price */}
        <div className="flex items-baseline gap-3">
          {product.is_on_sale && (
            <span className="text-2xl font-black text-red-600">
              {activePrice.toLocaleString()} EGP
            </span>
          )}
          <span
            className={`font-black ${
              product.is_on_sale
                ? 'text-lg text-gray-400 line-through'
                : 'text-2xl'
            }`}
          >
            {product.base_price.toLocaleString()} EGP
          </span>
        </div>

        {/* Size selector */}
        {product.sizes.length > 0 && (
          <div>
            <label className="text-sm font-bold mb-2 block">Size</label>
            <div className="flex flex-wrap gap-2">
              {product.sizes.map((size) => {
                const inStock = availableSizes.includes(size);
                return (
                  <button
                    key={size}
                    disabled={!inStock}
                    onClick={() => setSelectedSize(size)}
                    className={`min-w-[3rem] px-4 py-2 rounded-lg text-sm font-bold border transition ${
                      selectedSize === size
                        ? 'bg-black text-white border-black dark:bg-white dark:text-black dark:border-white'
                        : inStock
                        ? 'bg-white text-gray-700 border-gray-300 hover:border-black dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600'
                        : 'bg-gray-50 text-gray-300 border-gray-100 cursor-not-allowed dark:bg-gray-900 dark:text-gray-700 dark:border-gray-800'
                    }`}
                  >
                    {size}
                  </button>
                );
              })}
            </div>
          </div>
        )}

        {/* Quantity */}
        <div>
          <label className="text-sm font-bold mb-2 block">Quantity</label>
          <div className="flex items-center gap-3">
            <button
              onClick={() => setQuantity(Math.max(1, quantity - 1))}
              className="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center text-lg font-bold hover:bg-gray-100 transition dark:border-gray-600 dark:hover:bg-gray-700"
            >
              −
            </button>
            <span className="w-12 text-center text-xl font-black">{quantity}</span>
            <button
              onClick={() =>
                setQuantity(Math.min(selectedVariant?.stock ?? 99, quantity + 1))
              }
              disabled={quantity >= (selectedVariant?.stock ?? 99)}
              className="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center text-lg font-bold hover:bg-gray-100 transition disabled:opacity-30 dark:border-gray-600 dark:hover:bg-gray-700"
            >
              +
            </button>
            {selectedVariant && (
              <span className="text-xs text-gray-400">
                {selectedVariant.stock} in stock
              </span>
            )}
          </div>
        </div>

        {/* Add to cart */}
        <button
          onClick={() => {
            if (selectedVariant) onAddToCart(selectedVariant.id, quantity);
          }}
          disabled={!selectedVariant || selectedVariant.stock === 0}
          className="w-full py-4 bg-black text-white font-extrabold text-sm rounded-xl hover:bg-gray-800 transition disabled:opacity-40 disabled:cursor-not-allowed active:scale-[0.98]"
        >
          {selectedVariant?.stock === 0
            ? 'Out of Stock'
            : selectedVariant
            ? 'Add to Cart'
            : 'Select options'}
        </button>

        {/* Description */}
        {product.description && (
          <div>
            <h3 className="text-sm font-bold mb-2">Description</h3>
            <p className="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
              {product.description}
            </p>
          </div>
        )}
      </div>
    </div>
  );
}
