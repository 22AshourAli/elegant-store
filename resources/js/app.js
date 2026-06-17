import './bootstrap';
import Alpine from 'alpinejs';
import productView from './components/productView';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('productView', productView);
});

Alpine.start();
