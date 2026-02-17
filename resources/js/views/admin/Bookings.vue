<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Manage Bookings</h1>
        <p class="text-gray-500 mt-1">View and manage all customer bookings</p>
      </div>
      <select v-model="statusFilter" @change="loadBookings" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
        <option value="">All Statuses</option>
        <option value="pending">Pending</option>
        <option value="confirmed">Confirmed</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="bookings.length === 0" class="text-center py-16">
      <h3 class="text-lg font-semibold text-gray-600">No bookings found</h3>
    </div>

    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Booking #</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Customer</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Boat</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Dates</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Amount</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="booking in bookings" :key="booking.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 text-sm font-medium text-gray-900">#{{ booking.id }}</td>
            <td class="px-6 py-4">
              <p class="text-sm font-medium text-gray-900">{{ booking.user?.name || 'N/A' }}</p>
              <p class="text-xs text-gray-500">{{ booking.user?.email }}</p>
            </td>
            <td class="px-6 py-4 text-sm text-gray-600 capitalize">{{ booking.boat?.name }}</td>
            <td class="px-6 py-4 text-sm text-gray-600">
              <p>{{ new Date(booking.start_date).toLocaleDateString() }}</p>
              <p class="text-xs text-gray-400">to {{ new Date(booking.end_date).toLocaleDateString() }}</p>
            </td>
            <td class="px-6 py-4 text-sm font-semibold text-gray-900">${{ booking.total_price }}</td>
            <td class="px-6 py-4">
              <span :class="statusClass(booking.status)" class="px-2 py-1 rounded-full text-xs font-medium capitalize">
                {{ booking.status }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex items-center justify-end space-x-2">
                <button v-if="booking.status === 'pending'" @click="confirmBooking(booking)" class="text-green-600 hover:text-green-800 text-sm font-medium">Confirm</button>
                <button v-if="booking.status === 'confirmed'" @click="completeBooking(booking)" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Complete</button>
                <button v-if="booking.status === 'pending' || booking.status === 'confirmed'" @click="cancelBooking(booking)" class="text-red-600 hover:text-red-800 text-sm font-medium">Cancel</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="pagination.lastPage > 1" class="flex justify-center py-4 border-t border-gray-100 space-x-2">
        <button v-for="page in pagination.lastPage" :key="page" @click="loadBookings(page)" :class="['px-3 py-1 rounded text-sm font-medium', page === pagination.currentPage ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100']">
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
const statusFilter = ref('');
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
    const params = { page };
    if (statusFilter.value) params.status = statusFilter.value;
    const response = await api.get('/admin/bookings', { params });
    bookings.value = response.data.data || [];
    pagination.currentPage = response.data.current_page;
    pagination.lastPage = response.data.last_page;
  } catch (e) {
    console.error('Failed to load bookings:', e);
  } finally {
    loading.value = false;
  }
};

const confirmBooking = async (booking) => {
  try {
    await api.post(`/bookings/${booking.id}/confirm`);
    booking.status = 'confirmed';
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to confirm booking');
  }
};

const completeBooking = async (booking) => {
  try {
    await api.post(`/bookings/${booking.id}/complete`);
    booking.status = 'completed';
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to complete booking');
  }
};

const cancelBooking = async (booking) => {
  if (!confirm('Are you sure you want to cancel this booking?')) return;
  try {
    await api.post(`/bookings/${booking.id}/cancel`);
    booking.status = 'cancelled';
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to cancel booking');
  }
};

onMounted(() => loadBookings());
</script>
