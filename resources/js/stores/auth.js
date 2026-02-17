import { defineStore } from 'pinia';
import api from '../services/api';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('auth_user') || 'null'),
        token: localStorage.getItem('auth_token') || null,
        loading: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        isAdmin: (state) => state.user?.role === 'admin',
        userName: (state) => state.user?.name || '',
    },

    actions: {
        async register(data) {
            this.loading = true;
            try {
                const response = await api.post('/auth/register', data);
                this.setAuth(response.data);
                return response.data;
            } finally {
                this.loading = false;
            }
        },

        async login(credentials) {
            this.loading = true;
            try {
                const response = await api.post('/auth/login', credentials);
                this.setAuth(response.data);
                return response.data;
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            try {
                await api.post('/auth/logout');
            } catch (e) {
                // ignore
            }
            this.clearAuth();
        },

        async checkAuth() {
            if (!this.token) return;
            try {
                const response = await api.get('/auth/user');
                this.user = response.data;
                localStorage.setItem('auth_user', JSON.stringify(response.data));
            } catch {
                this.clearAuth();
            }
        },

        setAuth(data) {
            this.user = data.user;
            this.token = data.token;
            localStorage.setItem('auth_token', data.token);
            localStorage.setItem('auth_user', JSON.stringify(data.user));
        },

        clearAuth() {
            this.user = null;
            this.token = null;
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
        },
    },
});
