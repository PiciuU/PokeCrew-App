import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            name: 'Home',
            path: '/',
            component: () => import('@/views/Homepage.vue')
        },
        {
            name: 'Redirect',
            path: '/:catchAll(.*)',
            component: () => import('@/views/Homepage.vue')
        }
    ]
})

export default router
