import { createApp } from 'vue'
import { createI18n } from "vue-i18n";
import App from './App.vue'
import router from './router'
import axios from 'axios';

/* Styles */
import '@/assets/styles/main.scss'

/* Locales */
import pl from "@/assets/locales/pl.json";
import en from "@/assets/locales/en.json";

const i18n = createI18n({
    locale: navigator.language,
    fallbackLocale: "en",
    messages: { pl, en },
});

/* Axios */
axios.defaults.baseURL = import.meta.env.VITE_APP_API_URL;

const app = createApp(App)

app.use(router).use(i18n).mount('#app')
