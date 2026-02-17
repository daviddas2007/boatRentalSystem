<template>
  <nav class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex items-center">
          <router-link to="/" class="flex items-center space-x-2">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15l4-8 4 8M5 13h6M17 9v6m-3-3h6" />
            </svg>
            <span class="text-xl font-bold text-gray-900">BoatRental</span>
          </router-link>
          <div class="hidden md:flex items-center ml-10 space-x-8">
            <router-link to="/boats" class="text-gray-600 hover:text-blue-600 font-medium transition">Browse Boats</router-link>
            <router-link v-if="authStore.isAuthenticated" to="/my-bookings" class="text-gray-600 hover:text-blue-600 font-medium transition">My Bookings</router-link>
            <router-link v-if="authStore.isAdmin" to="/admin" class="text-gray-600 hover:text-blue-600 font-medium transition">Dashboard</router-link>
          </div>
        </div>
        <div class="flex items-center space-x-4">
          <template v-if="authStore.isAuthenticated">
            <div class="relative" ref="dropdownRef">
              <button @click="showDropdown = !showDropdown" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600 transition">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                  <span class="text-blue-600 font-semibold text-sm">{{ authStore.userName.charAt(0).toUpperCase() }}</span>
                </div>
                <span class="hidden md:block font-medium">{{ authStore.userName }}</span>
              </button>
              <div v-if="showDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                <router-link v-if="authStore.isAdmin" to="/admin" class="block px-4 py-2 text-gray-700 hover:bg-gray-50" @click="showDropdown = false">Admin Dashboard</router-link>
                <router-link to="/my-bookings" class="block px-4 py-2 text-gray-700 hover:bg-gray-50" @click="showDropdown = false">My Bookings</router-link>
                <button @click="handleLogout" class="block w-full text-left px-4 py-2 text-red-600 hover:bg-gray-50">Logout</button>
              </div>
            </div>
          </template>
          <template v-else>
            <router-link to="/login" class="text-gray-600 hover:text-blue-600 font-medium transition">Login</router-link>
            <router-link to="/register" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium transition">Sign Up</router-link>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();
const router = useRouter();
const showDropdown = ref(false);
const dropdownRef = ref(null);

const handleLogout = async () => {
  showDropdown.value = false;
  await authStore.logout();
  router.push('/');
};
</script>
