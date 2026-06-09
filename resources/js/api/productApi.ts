const API_BASE = '/api';

export async function fetchProductDetail(slug: string) {
  const res = await fetch(`${API_BASE}/products/${slug}`);
  if (!res.ok) throw new Error('Failed to load product');
  return res.json();
}

export async function fetchProducts(page = 1) {
  const res = await fetch(`${API_BASE}/products?page=${page}`);
  if (!res.ok) throw new Error('Failed to load products');
  return res.json();
}
