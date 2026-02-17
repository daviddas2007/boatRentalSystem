<template>
  <div>
    <section class="relative bg-gradient-to-r from-blue-700 to-blue-900 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32">
        <div class="max-w-3xl">
          <h1 class="text-4xl md:text-5xl font-bold mb-6 leading-tight">Find Your Perfect Boat Adventure</h1>
          <p class="text-xl text-blue-100 mb-8">Browse our selection of premium boats and set sail on your next adventure. From kayaks to yachts, we have it all.</p>
          <div class="flex flex-col sm:flex-row gap-4">
            <router-link to="/boats" class="bg-white text-blue-700 px-8 py-3 rounded-lg font-semibold hover:bg-blue-50 transition text-center">Browse Boats</router-link>
            <router-link to="/register" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-700 transition text-center">Get Started</router-link>
          </div>
        </div>
      </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <h2 class="text-3xl font-bold text-gray-900 mb-2 text-center">Boat Types</h2>
      <p class="text-gray-500 text-center mb-12">Choose from our wide variety of boats</p>
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
        <router-link v-for="type in boatTypes" :key="type.name" :to="`/boats?type=${type.value}`" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center hover:shadow-md hover:border-blue-200 transition group">
          <div class="text-4xl mb-3">{{ type.icon }}</div>
          <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition">{{ type.name }}</h3>
        </router-link>
      </div>
    </section>

    <section class="bg-white py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-2 text-center">Featured Boats</h2>
        <p class="text-gray-500 text-center mb-12">Our most popular rentals</p>
        <div v-if="loading" class="flex justify-center py-12">
          <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <router-link v-for="boat in featuredBoats" :key="boat.id" :to="`/boats/${boat.slug}`" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition group">
            <div class="h-48 bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
              <img v-if="boat.featured_image" :src="`/storage/${boat.featured_image}`" :alt="boat.name" class="w-full h-full object-cover" />
              <svg v-else class="w-16 h-16 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 15l4-8 4 8M5 13h6M17 9v6m-3-3h6" />
              </svg>
            </div>
            <div class="p-5">
              <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full capitalize">{{ boat.type }}</span>
                <div class="flex items-center text-yellow-500 text-sm">
                  <span>&#9733;</span>
                  <span class="ml-1 text-gray-600">{{ boat.average_rating || 'New' }}</span>
                </div>
              </div>
              <h3 class="font-bold text-gray-900 text-lg group-hover:text-blue-600 transition">{{ boat.name }}</h3>
              <p class="text-gray-500 text-sm mt-1">{{ boat.location }}</p>
              <div class="flex items-center justify-between mt-4">
                <span class="text-blue-600 font-bold text-lg">${{ boat.price_per_hour }}<span class="text-gray-400 text-sm font-normal">/hr</span></span>
                <span class="text-gray-500 text-sm">{{ boat.capacity }} guests</span>
              </div>
            </div>
          </router-link>
        </div>
        <div class="text-center mt-12">
          <router-link to="/boats" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">View All Boats</router-link>
        </div>
      </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <h2 class="text-3xl font-bold text-gray-900 mb-12 text-center">How It Works</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="text-center">
          <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-2xl">1</span>
          </div>
          <h3 class="font-bold text-lg mb-2">Browse & Choose</h3>
          <p class="text-gray-500">Explore our fleet and find the perfect boat for your adventure.</p>
        </div>
        <div class="text-center">
          <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-2xl">2</span>
          </div>
          <h3 class="font-bold text-lg mb-2">Book & Pay</h3>
          <p class="text-gray-500">Select your dates, make a secure payment, and confirm your booking.</p>
        </div>
        <div class="text-center">
          <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-2xl">3</span>
          </div>
          <h3 class="font-bold text-lg mb-2">Set Sail</h3>
          <p class="text-gray-500">Show up on your rental day and enjoy your time on the water!</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';

const featuredBoats = ref([]);
const loading = ref(true);

const boatTypes = [
  { name: 'Sailboat', value: 'sailboat', icon: '\u26F5' },
  { name: 'Yacht', value: 'yacht', icon: '\uD83D\uDEA2' },
  { name: 'Speedboat', value: 'speedboat', icon: '\uD83D\uDEA4' },
  { name: 'Kayak', value: 'kayak', icon: '\uD83D\uDEF6' },
  { name: 'Pontoon', value: 'pontoon', icon: '\u2693' },
  { name: 'Catamaran', value: 'catamaran', icon: '\uD83C\uDFDD\uFE0F' },
];

onMounted(async () => {
  try {
    const response = await api.get('/boats', { params: { per_page: 6 } });
    featuredBoats.value = response.data.data || [];
  } catch (e) {
    console.error('Failed to load boats:', e);
  } finally {
    loading.value = false;
  }
});
</script>
