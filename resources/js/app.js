import './bootstrap';
import { createApp, h, ref } from 'vue';
import { createInertiaApp, Link, router } from '@inertiajs/vue3';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        
        app.use(plugin);
        
        // Agregar token a todos los requests de Inertia
        // Interceptar en beforeVisit
        router.on('before', (event) => {
            const token = localStorage.getItem('auth_token');
            if (token) {
                event.visit.headers = {
                    ...event.visit.headers,
                    'Authorization': `Bearer ${token}`
                };
            }
        });
        
        app.mount(el);
    },
});
