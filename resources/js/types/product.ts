export interface Variant {
  id: number;
  sku: string;
  color: string | null;
  size: string | null;
  price: number;
  stock: number;
  image: string | null;
  is_default: boolean;
}

export interface ColorGallery {
  [color: string]: string[];
}

export interface ProductDetail {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  base_price: number;
  current_price: number;
  is_on_sale: boolean;
  has_variants: boolean;
  category: { id: number; name: string; slug: string } | null;
  colors: string[];
  sizes: string[];
  color_galleries: ColorGallery;
  default_image: string;
  all_images: string[];
  variants: Variant[];
}

export interface ProductCard {
  id: number;
  name: string;
  slug: string;
  base_price: number;
  current_price: number;
  is_on_sale: boolean;
  image: string;
  colors: string[];
  has_variants: boolean;
  category_id: number;
}
