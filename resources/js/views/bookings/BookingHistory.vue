<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">My Bookings</h1>

    <div v-if="loading" class="flex justify-center py-16">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="bookings.length === 0" class="text-center py-16">
      <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 5a2 2 0 002 2h2a2 2 0 002-2" />
      </svg>
      <h3 class="text-lg font-semibold text-gray-600">No bookings yet</h3>
      <p class="text-gray-400 mt-1 mb-4">Browse our boats and make your first booking!</p>
      <router-link to="/boats" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition">Browse Boats</router-link>
    </div>

    <div v-else class="space-y-4">
      <router-link v-for="booking in bookings" :key="booking.id" :to="`/bookings/${booking.id}`" class="block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div class="flex items-center mb-4 md:mb-0">
            <div class="w-16 h-14 bg-blue-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
              <img v-if="booking.boat?.featured_image" :src="`/storage/${booking.boat.featured_image}`" :alt="booking.boat?.name" class="w-full h-full object-cover rounded-lg" />
              <span v-else class="text-xl">&#9973;</span>
            </div>
            <div>
              <h3 class="font-bold text-gray-900">{{ booking.boat?.name || 'Unknown Boat' }}</h3>
              <p class="text-gray-500 text-sm capitalize">{{ booking.boat?.type }} &middot; {{ booking.boat?.location }}</p>
              <p class="text-gray-400 text-xs mt-1">
                {{ new Date(booking.start_date).toLocaleDateString() }} - {{ new Date(booking.end_date).toLocaleDateString() }}
              </p>
            </div>
          </div>
          <div class="flex items-center space-x-6">
            <div class="text-right">
              <p class="text-lg font-bold text-gray-900">${{ booking.total_price }}</p>
              <p class="text-gray-400 text-xs">{{ booking.duration_value }} {{ booking.duration_type === 'hourly' ? 'hr(s)' : 'day(s)' }}</p>
            </div>
            <span :class="statusClass(booking.status)" class="px-3 py-1 rounded-full text-xs font-semibold capitalize">
              {{ booking.status }}
            </span>
          </div>
        </div>
      </router-link>

      <div v-if="pagination.lastPage > 1" class="flex justify-center mt-6 space-x-2">
        <button v-for="page in pagination.lastPage" :key="page" @click="loadBookings(page)" :class="['px-4 py-2 rounded-lg text-sm font-medium transition', page === pagination.currentPage ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50']">
          {{ page }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../services/api';

const bookings = ref([]);
const loading = ref(true);
const pagination = reactive({ currentPage: 1, lastPage: 1 });

const statusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
    completed: 'bg-blue-100 text-blue-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const loadBookings = async (page = 1) => {
  loading.value = true;
  try {
    const response = await api.get('/bookings', { params: { page } });
    bookings.value = response.data.data || [];
    pagination.currentPage = response.data.current_page;
    pagination.lastPage = response.data.last_page;
  } catch (e) {
    console.error('Failed to load bookings:', e);
  } finally {
    loading.value = false;
  }
};

onMounted(() => loadBookings());
</script>
