import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import '../css/app.css'; // minimal css
import './echo';
import App from './pages/App.vue';

// create pinia instance
const pinia = createPinia()
const app = createApp(App);

app.use(pinia);
app.use(router);
app.mount('#app');