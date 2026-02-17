<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Browse Boats</h1>
        <p class="text-gray-500 mt-1">Find the perfect boat for your next adventure</p>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
          <input v-model="filters.search" @input="debouncedSearch" type="text" placeholder="Search boats..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" />
        </div>
        <div>
          <select v-model="filters.type" @change="loadBoats" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            <option value="">All Types</option>
            <option value="sailboat">Sailboat</option>
            <option value="yacht">Yacht</option>
            <option value="speedboat">Speedboat</option>
            <option value="kayak">Kayak</option>
            <option value="pontoon">Pontoon</option>
            <option value="catamaran">Catamaran</option>
          </select>
        </div>
        <div>
          <select v-model="filters.capacity" @change="loadBoats" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            <option value="">Any Capacity</option>
            <option value="2">2+ guests</option>
            <option value="4">4+ guests</option>
            <option value="6">6+ guests</option>
            <option value="10">10+ guests</option>
            <option value="20">20+ guests</option>
          </select>
        </div>
        <div>
          <select v-model="filters.sort_by" @change="loadBoats" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            <option value="created_at">Newest</option>
            <option value="price_per_hour">Price: Low to High</option>
            <option value="capacity">Capacity</option>
            <option value="name">Name</option>
          </select>
        </div>
        <div>
          <button @click="clearFilters" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition">Clear Filters</button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="boats.length === 0" class="text-center py-16">
      <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
      <h3 class="text-lg font-semibold text-gray-600">No boats found</h3>
      <p class="text-gray-400 mt-1">Try adjusting your filters</p>
    </div>

    <div v-else>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <router-link v-for="boat in boats" :key="boat.id" :to="`/boats/${boat.slug}`" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition group">
          <div class="h-48 bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center relative">
            <img v-if="boat.featured_image" :src="`/storage/${boat.featured_image}`" :alt="boat.name" class="w-full h-full object-cover" />
            <svg v-else class="w-16 h-16 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 15l4-8 4 8M5 13h6M17 9v6m-3-3h6" />
            </svg>
          </div>
          <div class="p-5">
            <div class="flex items-center justify-between mb-2">
              <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full capitalize">{{ boat.type }}</span>
              <div class="flex items-center text-sm">
                <span class="text-yellow-500">&#9733;</span>
                <span class="ml-1 text-gray-600">{{ boat.average_rating > 0 ? boat.average_rating : 'New' }}</span>
                <span v-if="boat.review_count > 0" class="text-gray-400 ml-1">({{ boat.review_count }})</span>
              </div>
            </div>
            <h3 class="font-bold text-gray-900 text-lg group-hover:text-blue-600 transition">{{ boat.name }}</h3>
            <p class="text-gray-500 text-sm mt-1">{{ boat.location }}</p>
            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
              <div>
                <span class="text-blue-600 font-bold text-lg">${{ boat.price_per_hour }}</span>
                <span class="text-gray-400 text-sm">/hr</span>
                <span class="text-gray-300 mx-1">|</span>
                <span class="text-gray-600 font-semibold">${{ boat.price_per_day }}</span>
                <span class="text-gray-400 text-sm">/day</span>
              </div>
              <span class="text-gray-500 text-sm">{{ boat.capacity }} guests</span>
            </div>
          </div>
        </router-link>
      </div>

      <div v-if="pagination.lastPage > 1" class="flex justify-center mt-8 space-x-2">
        <button v-for="page in pagination.lastPage" :key="page" @click="goToPage(page)" :class="['px-4 py-2 rounded-lg text-sm font-medium transition', page === pagination.currentPage ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50']">
          {{ page }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../services/api';

const route = useRoute();
const boats = ref([]);
const loading = ref(true);
const pagination = reactive({ currentPage: 1, lastPage: 1, total: 0 });

const filters = reactive({
  search: '',
  type: route.query.type || '',
  capacity: '',
  sort_by: 'created_at',
  sort_dir: 'desc',
});

let debounceTimer = null;
const debouncedSearch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => loadBoats(), 300);
};

const loadBoats = async (page = 1) => {
  loading.value = true;
  try {
    const params = { page, per_page: 12 };
    if (filters.search) params.search = filters.search;
    if (filters.type) params.type = filters.type;
    if (filters.capacity) params.capacity = filters.capacity;
    if (filters.sort_by) {
      params.sort_by = filters.sort_by;
      params.sort_dir = filters.sort_by === 'price_per_hour' ? 'asc' : 'desc';
    }
    const response = await api.get('/boats', { params });
    boats.value = response.data.data || [];
    pagination.currentPage = response.data.current_page;
    pagination.lastPage = response.data.last_page;
    pagination.total = response.data.total;
  } catch (e) {
    console.error('Failed to load boats:', e);
  } finally {
    loading.value = false;
  }
};

const goToPage = (page) => loadBoats(page);

const clearFilters = () => {
  filters.search = '';
  filters.type = '';
  filters.capacity = '';
  filters.sort_by = 'created_at';
  loadBoats();
};

onMounted(() => loadBoats());
</script>
