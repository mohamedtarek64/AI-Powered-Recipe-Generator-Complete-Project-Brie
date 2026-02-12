import './css/app.css';
import { createApp } from 'vue';
import App from './App.vue';
import { initializeTheme } from './composables/useAppearance';
import { router, route } from './lib/route';
import axiosLib from 'axios';
window.route = route;
const app = createApp(App);
app.use(router);
// Add route to global properties for templates
app.config.globalProperties.route = route;
// Initialize CSRF protection (background)
axiosLib.get('/sanctum/csrf-cookie', { baseURL: 'http://localhost:3000' }).catch(err => {
    console.warn('CSRF Initialization Error (Non-Fatal):', err);
});
try {
    // Mount immediately
    app.mount('#app');
    console.log('App mounted successfully');
}
catch (e) {
    console.error('App Mount Error:', e);
    document.body.innerHTML = '<div style="color:red; font-size: 20px; padding: 20px;">CRITICAL ERROR: ' + e + '</div>';
}
initializeTheme();
//# sourceMappingURL=app.js.map