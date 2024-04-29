import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import axios from 'axios';

/* Styles */
import '@/assets/styles/main.scss'

axios.defaults.baseURL = import.meta.env.VITE_APP_API_URL;

const app = createApp(App)

app.use(router).mount('#app')
