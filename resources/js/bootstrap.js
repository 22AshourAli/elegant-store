import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

try {
    import('laravel-echo').then(({ default: Echo }) => {
        import('pusher-js').then(({ default: Pusher }) => {
            window.Pusher = Pusher;
            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: import.meta.env.VITE_REVERB_APP_KEY,
                wsHost: import.meta.env.VITE_REVERB_HOST ?? 'localhost',
                wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
                wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
                forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
                enabledTransports: ['ws', 'wss'],
            });

            window.Echo.channel('stock')
                .listen('.StockUpdated', (e) => {
                    window.dispatchEvent(new CustomEvent('stock-updated', { detail: e }));
                });
        }).catch(e => { console.error('Failed to load Pusher:', e); });
    }).catch(e => { console.error('Failed to load Echo:', e); });
} catch (e) { console.warn('Realtime imports are not available:', e); }
