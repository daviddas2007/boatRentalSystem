import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

import Home from '../views/Home.vue';
import Login from '../views/auth/Login.vue';
import Register from '../views/auth/Register.vue';
import BoatList from '../views/boats/BoatList.vue';
import BoatDetail from '../views/boats/BoatDetail.vue';
import BookingCreate from '../views/bookings/BookingCreate.vue';
import BookingHistory from '../views/bookings/BookingHistory.vue';
import BookingDetail from '../views/bookings/BookingDetail.vue';
import BookingPayment from '../views/bookings/BookingPayment.vue';
import AdminDashboard from '../views/admin/Dashboard.vue';
import AdminBoats from '../views/admin/Boats.vue';
import AdminBoatForm from '../views/admin/BoatForm.vue';
import AdminBookings from '../views/admin/Bookings.vue';

const routes = [
    { path: '/', name: 'home', component: Home },
    { path: '/login', name: 'login', component: Login, meta: { guest: true } },
    { path: '/register', name: 'register', component: Register, meta: { guest: true } },
    { path: '/boats', name: 'boats', component: BoatList },
    { path: '/boats/:slug', name: 'boat-detail', component: BoatDetail, props: true },
    { path: '/book/:slug', name: 'booking-create', component: BookingCreate, props: true, meta: { auth: true } },
    { path: '/my-bookings', name: 'booking-history', component: BookingHistory, meta: { auth: true } },
    { path: '/bookings/:id', name: 'booking-detail', component: BookingDetail, props: true, meta: { auth: true } },
    { path: '/bookings/:id/payment', name: 'booking-payment', component: BookingPayment, props: true, meta: { auth: true } },
    { path: '/admin', name: 'admin-dashboard', component: AdminDashboard, meta: { admin: true } },
    { path: '/admin/boats', name: 'admin-boats', component: AdminBoats, meta: { admin: true } },
    { path: '/admin/boats/create', name: 'admin-boat-create', component: AdminBoatForm, meta: { admin: true } },
    { path: '/admin/boats/:id/edit', name: 'admin-boat-edit', component: AdminBoatForm, props: true, meta: { admin: true } },
    { path: '/admin/bookings', name: 'admin-bookings', component: AdminBookings, meta: { admin: true } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();

    if (to.meta.auth && !authStore.isAuthenticated) {
        return next({ name: 'login', query: { redirect: to.fullPath } });
    }

    if (to.meta.admin && (!authStore.isAuthenticated || !authStore.isAdmin)) {
        return next({ name: 'home' });
    }

    if (to.meta.guest && authStore.isAuthenticated) {
        return next({ name: 'home' });
    }

    next();
});

export default router;
