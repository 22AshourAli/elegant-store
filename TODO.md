# Elegant Store - Implementation TODO

## Phase 1 (Diagnostics & Quick Fixes)
- [x] Read storefront layout/navigation/auth/cart/checkout.
- [x] Identify missing shipping/free-shipping logic.
- [x] Identify price decimal formatting sources.
- [x] Confirm notifications: Mail + Database via existing notification classes.
- [x] Verify order status translation keys exist.

## Phase 2 (UI/UX + Core correctness fixes)
- [ ] Round/format prices to clean integers everywhere:
  - [ ] `resources/views/shop/cart.blade.php` (JS `Intl` formatting + totals parsing)
  - [ ] `resources/views/shop/checkout.blade.php` (all `number_format(..., 2)` -> integer)
  - [ ] `resources/views/shop/orders/show.blade.php` (totals + unit prices)
  - [ ] `resources/views/admin/orders/index.blade.php` (totals)
  - [ ] `resources/views/admin/orders/show.blade.php` (totals)
  - [ ] `app/Http/Controllers/Shop/CartController.php` (JSON totals)
- [ ] Add per-field validation error UI for checkout.

## Phase 3 (i18n)
- [ ] Fix any admin-language/locale mismatch causing translation keys to appear literally.

## Phase 4 (Shipping + Notifications)
- [ ] Implement smart shipping (free first order; dynamic later by address).
- [ ] Extend external notifications (Email + WhatsApp/SMS) for order status changes.
- [ ] Ensure admin external notification triggers on new order.

## Phase 5 (Admin UX/workflow)
- [ ] Verify admin explicit cancel capability + authorization/policy.
- [ ] Fix admin logout redirect.
- [ ] Fix store-logo navigation separation (admin -> storefront and return button behavior).

## Phase 6 (Product/Variant & Admin forms)
- [ ] Restrict product image upload file picker (png/jpg/jpeg/webp).
- [ ] Fix variant image mapping bug in product details.

## Phase 7 (Coupons + Inventory)
- [ ] Add customer-facing coupons discovery/apply UI.
- [ ] Set low stock threshold default to < 2 in admin.

