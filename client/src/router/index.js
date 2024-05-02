export const router = [
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
];