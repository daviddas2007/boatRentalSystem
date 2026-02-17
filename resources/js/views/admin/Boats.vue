<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Manage Boats</h1>
        <p class="text-gray-500 mt-1">Add, edit, and manage your fleet</p>
      </div>
      <router-link to="/admin/boats/create" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition">+ Add Boat</router-link>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="boats.length === 0" class="text-center py-16">
      <h3 class="text-lg font-semibold text-gray-600">No boats added yet</h3>
      <p class="text-gray-400 mt-1 mb-4">Start by adding your first boat</p>
      <router-link to="/admin/boats/create" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition">Add Your First Boat</router-link>
    </div>

    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Boat</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Price</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Capacity</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Bookings</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-for="boat in boats" :key="boat.id" class="hover:bg-gray-50">
            <td class="px-6 py-4">
              <div class="flex items-center">
                <div class="w-12 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                  <img v-if="boat.featured_image" :src="`/storage/${boat.featured_image}`" class="w-full h-full object-cover rounded-lg" />
                  <span v-else class="text-blue-400 text-sm">&#9973;</span>
                </div>
                <div>
                  <p class="font-medium text-gray-900">{{ boat.name }}</p>
                  <p class="text-sm text-gray-500">{{ boat.location }}</p>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 capitalize text-sm text-gray-600">{{ boat.type }}</td>
            <td class="px-6 py-4 text-sm">
              <span class="font-medium">${{ boat.price_per_hour }}/hr</span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-600">{{ boat.capacity }}</td>
            <td class="px-6 py-4 text-sm text-gray-600">{{ boat.bookings_count || 0 }}</td>
            <td class="px-6 py-4">
              <span :class="boat.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'" class="px-2 py-1 rounded-full text-xs font-medium">
                {{ boat.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex items-center justify-end space-x-2">
                <router-link :to="`/admin/boats/${boat.id}/edit`" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Edit</router-link>
                <button @click="toggleStatus(boat)" class="text-gray-500 hover:text-gray-700 text-sm font-medium">
                  {{ boat.is_active ? 'Deactivate' : 'Activate' }}
                </button>
                <button @click="deleteBoat(boat)" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="pagination.lastPage > 1" class="flex justify-center py-4 border-t border-gray-100 space-x-2">
        <button v-for="page in pagination.lastPage" :key="page" @click="loadBoats(page)" :class="['px-3 py-1 rounded text-sm font-medium', page === pagination.currentPage ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100']">
          {{ page }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../services/api';

const boats = ref([]);
const loading = ref(true);
const pagination = reactive({ currentPage: 1, lastPage: 1 });

const loadBoats = async (page = 1) => {
  loading.value = true;
  try {
    const response = await api.get('/my-boats', { params: { page } });
    boats.value = response.data.data || [];
    pagination.currentPage = response.data.current_page;
    pagination.lastPage = response.data.last_page;
  } catch (e) {
    console.error('Failed to load boats:', e);
  } finally {
    loading.value = false;
  }
};

const toggleStatus = async (boat) => {
  try {
    await api.put(`/boats/${boat.id}`, { is_active: !boat.is_active });
    boat.is_active = !boat.is_active;
  } catch (e) {
    alert('Failed to update boat status');
  }
};

const deleteBoat = async (boat) => {
  if (!confirm(`Are you sure you want to delete "${boat.name}"?`)) return;
  try {
    await api.delete(`/boats/${boat.id}`);
    boats.value = boats.value.filter(b => b.id !== boat.id);
  } catch (e) {
    alert('Failed to delete boat');
  }
};

onMounted(() => loadBoats());
</script>
