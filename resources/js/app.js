import './bootstrap';
import Alpine from 'alpinejs';
import productView from './components/productView';

window.Alpine = Alpine;

export function formatPrice(price) {
    const value = Math.round(parseFloat(price || 0));
    return value.toLocaleString() + ' EGP';
}

document.addEventListener('alpine:init', () => {
    Alpine.data('productView', productView);
});

Alpine.start();
