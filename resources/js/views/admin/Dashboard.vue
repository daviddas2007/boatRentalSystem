<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="text-gray-500 mt-1">Overview of your boating rental business</p>
      </div>
      <div class="flex space-x-3">
        <router-link to="/admin/boats" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition">Manage Boats</router-link>
        <router-link to="/admin/bookings" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition">Manage Bookings</router-link>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
    </div>

    <div v-else>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Total Revenue</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">${{ formatNumber(stats.total_revenue) }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
              <span class="text-green-600 text-xl">$</span>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Total Bookings</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_bookings }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
              <span class="text-blue-600 text-xl">&#128203;</span>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Active Rentals</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.active_rentals }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
              <span class="text-yellow-600 text-xl">&#9973;</span>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Total Customers</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_customers }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
              <span class="text-purple-600 text-xl">&#128100;</span>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Booking Status</h2>
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-yellow-50 rounded-lg p-4 text-center">
              <p class="text-2xl font-bold text-yellow-600">{{ stats.pending_bookings }}</p>
              <p class="text-sm text-yellow-700">Pending</p>
            </div>
            <div class="bg-green-50 rounded-lg p-4 text-center">
              <p class="text-2xl font-bold text-green-600">{{ stats.active_rentals }}</p>
              <p class="text-sm text-green-700">Confirmed</p>
            </div>
            <div class="bg-blue-50 rounded-lg p-4 text-center">
              <p class="text-2xl font-bold text-blue-600">{{ stats.completed_bookings }}</p>
              <p class="text-sm text-blue-700">Completed</p>
            </div>
            <div class="bg-red-50 rounded-lg p-4 text-center">
              <p class="text-2xl font-bold text-red-600">{{ stats.cancelled_bookings }}</p>
              <p class="text-sm text-red-700">Cancelled</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Monthly Revenue ({{ currentYear }})</h2>
          <div class="space-y-2">
            <div v-for="month in monthlyRevenue" :key="month.month_number" class="flex items-center">
              <span class="text-sm text-gray-500 w-10">{{ month.month }}</span>
              <div class="flex-1 mx-3 bg-gray-100 rounded-full h-4 overflow-hidden">
                <div class="bg-blue-600 h-full rounded-full transition-all" :style="{ width: getBarWidth(month.revenue) }"></div>
              </div>
              <span class="text-sm font-medium text-gray-700 w-20 text-right">${{ formatNumber(month.revenue) }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Most Popular Boats</h2>
          <div v-if="popularBoats.length > 0" class="space-y-3">
            <div v-for="(boat, index) in popularBoats" :key="boat.id" class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
              <div class="flex items-center">
                <span class="text-sm font-bold text-gray-400 w-6">#{{ index + 1 }}</span>
                <div class="ml-3">
                  <p class="font-medium text-gray-900">{{ boat.name }}</p>
                  <p class="text-sm text-gray-500 capitalize">{{ boat.type }} &middot; {{ boat.location }}</p>
                </div>
              </div>
              <div class="text-right">
                <p class="font-semibold text-gray-900">{{ boat.bookings_count }} bookings</p>
                <p class="text-sm text-gray-500">${{ boat.price_per_hour }}/hr</p>
              </div>
            </div>
          </div>
          <p v-else class="text-gray-400 text-center py-4">No data yet</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Recent Bookings</h2>
          <div v-if="recentBookings.length > 0" class="space-y-3">
            <div v-for="booking in recentBookings" :key="booking.id" class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
              <div>
                <p class="font-medium text-gray-900">{{ booking.user?.name || 'N/A' }}</p>
                <p class="text-sm text-gray-500">{{ booking.boat?.name }} &middot; {{ new Date(booking.created_at).toLocaleDateString() }}</p>
              </div>
              <div class="text-right">
                <p class="font-semibold text-gray-900">${{ booking.total_price }}</p>
                <span :class="statusClass(booking.status)" class="text-xs font-medium px-2 py-0.5 rounded-full capitalize">{{ booking.status }}</span>
              </div>
            </div>
          </div>
          <p v-else class="text-gray-400 text-center py-4">No bookings yet</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';

const loading = ref(true);
const stats = ref({});
const monthlyRevenue = ref([]);
const popularBoats = ref([]);
const recentBookings = ref([]);
const currentYear = new Date().getFullYear();

const formatNumber = (num) => {
  if (!num) return '0';
  return Number(num).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
};

const getBarWidth = (revenue) => {
  const max = Math.max(...monthlyRevenue.value.map(m => m.revenue), 1);
  return `${(revenue / max) * 100}%`;
};

const statusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
    completed: 'bg-blue-100 text-blue-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

onMounted(async () => {
  try {
    const [statsRes, revenueRes, boatsRes, bookingsRes] = await Promise.all([
      api.get('/admin/stats'),
      api.get('/admin/monthly-revenue'),
      api.get('/admin/popular-boats'),
      api.get('/admin/recent-bookings'),
    ]);
    stats.value = statsRes.data;
    monthlyRevenue.value = revenueRes.data;
    popularBoats.value = boatsRes.data;
    recentBookings.value = bookingsRes.data;
  } catch (e) {
    console.error('Failed to load dashboard data:', e);
  } finally {
    loading.value = false;
  }
});
</script>
