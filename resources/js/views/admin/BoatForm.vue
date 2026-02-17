<template>
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
      <router-link to="/admin/boats" class="text-blue-600 hover:underline text-sm">&larr; Back to boats</router-link>
    </div>

    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ isEdit ? 'Edit Boat' : 'Add New Boat' }}</h1>

    <form @submit.prevent="handleSubmit" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
      <div v-if="error" class="bg-red-50 text-red-600 p-3 rounded-lg mb-6 text-sm">{{ error }}</div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Boat Name</label>
          <input v-model="form.name" type="text" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="e.g., Ocean Breeze" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
          <select v-model="form.type" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            <option value="">Select type</option>
            <option value="sailboat">Sailboat</option>
            <option value="yacht">Yacht</option>
            <option value="speedboat">Speedboat</option>
            <option value="kayak">Kayak</option>
            <option value="pontoon">Pontoon</option>
            <option value="catamaran">Catamaran</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Capacity (guests)</label>
          <input v-model.number="form.capacity" type="number" min="1" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Price per Hour ($)</label>
          <input v-model.number="form.price_per_hour" type="number" step="0.01" min="0" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Price per Day ($)</label>
          <input v-model.number="form.price_per_day" type="number" step="0.01" min="0" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" />
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
          <input v-model="form.location" type="text" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="e.g., Miami Marina, FL" />
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea v-model="form.description" rows="4" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Describe your boat..."></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Manufacturer</label>
          <input v-model="form.manufacturer" type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Year Built</label>
          <input v-model.number="form.year_built" type="number" min="1900" :max="new Date().getFullYear()" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Length (ft)</label>
          <input v-model.number="form.length_ft" type="number" step="0.1" min="0" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Featured Image</label>
          <input type="file" @change="handleImageUpload" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700 file:font-medium" />
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">Amenities (comma-separated)</label>
          <input v-model="amenitiesText" type="text" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="e.g., WiFi, GPS, Life jackets, Cooler" />
        </div>
      </div>

      <div class="flex space-x-4 mt-8">
        <button type="submit" :disabled="submitting" class="bg-blue-600 text-white px-8 py-2.5 rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50">
          {{ submitting ? 'Saving...' : (isEdit ? 'Update Boat' : 'Add Boat') }}
        </button>
        <router-link to="/admin/boats" class="border border-gray-300 text-gray-600 px-8 py-2.5 rounded-lg font-medium hover:bg-gray-50 transition">Cancel</router-link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';

const props = defineProps({ id: [String, Number] });
const router = useRouter();
const isEdit = computed(() => !!props.id);
const submitting = ref(false);
const error = ref('');
const amenitiesText = ref('');
const featuredImageFile = ref(null);

const form = ref({
  name: '',
  type: '',
  capacity: 1,
  price_per_hour: 0,
  price_per_day: 0,
  location: '',
  description: '',
  manufacturer: '',
  year_built: null,
  length_ft: null,
});

const handleImageUpload = (e) => {
  featuredImageFile.value = e.target.files[0];
};

const handleSubmit = async () => {
  submitting.value = true;
  error.value = '';

  try {
    const formData = new FormData();
    Object.entries(form.value).forEach(([key, val]) => {
      if (val !== null && val !== '') {
        formData.append(key, val);
      }
    });

    if (amenitiesText.value) {
      const amenities = amenitiesText.value.split(',').map(a => a.trim()).filter(Boolean);
      amenities.forEach((a, i) => formData.append(`amenities[${i}]`, a));
    }

    if (featuredImageFile.value) {
      formData.append('featured_image', featuredImageFile.value);
    }

    if (isEdit.value) {
      formData.append('_method', 'PUT');
      await api.post(`/boats/${props.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    } else {
      await api.post('/boats', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    }

    router.push('/admin/boats');
  } catch (e) {
    const errors = e.response?.data?.errors;
    if (errors) {
      error.value = Object.values(errors).flat().join(', ');
    } else {
      error.value = e.response?.data?.message || 'Failed to save boat.';
    }
  } finally {
    submitting.value = false;
  }
};

onMounted(async () => {
  if (isEdit.value) {
    try {
      const response = await api.get(`/boats/${props.id}`);
      const boat = response.data;
      form.value = {
        name: boat.name,
        type: boat.type,
        capacity: boat.capacity,
        price_per_hour: boat.price_per_hour,
        price_per_day: boat.price_per_day,
        location: boat.location,
        description: boat.description || '',
        manufacturer: boat.manufacturer || '',
        year_built: boat.year_built,
        length_ft: boat.length_ft,
      };
      if (boat.amenities) {
        amenitiesText.value = boat.amenities.join(', ');
      }
    } catch (e) {
      error.value = 'Failed to load boat data.';
    }
  }
});
</script>
