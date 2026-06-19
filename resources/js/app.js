import './bootstrap';
import Alpine from 'alpinejs';
import productView from './components/productView';

window.Alpine = Alpine;

export function formatPrice(price) {
    const value = Math.round(parseFloat(price || 0));
    const isAr = document.documentElement.lang === 'ar';
    const locale = isAr ? 'ar-EG' : 'en-US';
    const currency = isAr ? ' ج.م' : ' EGP';
    return value.toLocaleString(locale) + currency;
}

export function formatNumber(price) {
    const value = Math.round(parseFloat(price || 0));
    const isAr = document.documentElement.lang === 'ar';
    const locale = isAr ? 'ar-EG' : 'en-US';
    return value.toLocaleString(locale);
}

document.addEventListener('alpine:init', () => {
    Alpine.data('productView', productView);
});

Alpine.start();
