import { ViteSSG } from 'vite-ssg'
import { router } from './router'
import App from './App.vue'
import { createI18n } from "vue-i18n";
import axios from 'axios';


/* Styles */
import '@/assets/styles/main.scss'

/* Locales */
import pl from "@/assets/locales/pl.json";
import en from "@/assets/locales/en.json";

export const createApp = ViteSSG(
    App,
    { routes: router },
    ({ app }) => {
        axios.defaults.baseURL = import.meta.env.VITE_APP_API_URL;

        const i18n = createI18n({
            legacy: false,
            locale: "pl",
            fallbackLocale: "en",
            messages: { pl, en },
        });

        app.use(i18n);
    }
);