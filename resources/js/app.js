import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

if (window.Echo) {
    window.Echo.channel('stock')
        .listen('.StockUpdated', (e) => {
            window.dispatchEvent(new CustomEvent('stock-updated', { detail: e }));
        });
}
