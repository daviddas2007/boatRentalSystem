<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div v-if="loading" class="flex justify-center py-16">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="boat">
      <div class="mb-6">
        <router-link to="/boats" class="text-blue-600 hover:underline text-sm">&larr; Back to boats</router-link>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="h-72 md:h-96 bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
              <img v-if="boat.featured_image" :src="`/storage/${boat.featured_image}`" :alt="boat.name" class="w-full h-full object-cover" />
              <svg v-else class="w-24 h-24 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 15l4-8 4 8M5 13h6M17 9v6m-3-3h6" />
              </svg>
            </div>

            <div v-if="boat.images && boat.images.length > 0" class="flex overflow-x-auto p-4 space-x-3">
              <img v-for="img in boat.images" :key="img.id" :src="`/storage/${img.image_path}`" class="h-20 w-28 object-cover rounded-lg flex-shrink-0 border-2 border-transparent hover:border-blue-400 cursor-pointer" />
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Description</h2>
            <p class="text-gray-600 leading-relaxed">{{ boat.description || 'No description provided.' }}</p>

            <div v-if="boat.amenities && boat.amenities.length > 0" class="mt-6">
              <h3 class="font-semibold text-gray-900 mb-3">Amenities</h3>
              <div class="flex flex-wrap gap-2">
                <span v-for="amenity in boat.amenities" :key="amenity" class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm">{{ amenity }}</span>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-bold text-gray-900">Reviews</h2>
              <div class="flex items-center">
                <span class="text-yellow-500 text-xl">&#9733;</span>
                <span class="font-bold text-lg ml-1">{{ boat.average_rating }}</span>
                <span class="text-gray-400 ml-1">({{ boat.review_count }} reviews)</span>
              </div>
            </div>

            <div v-if="boat.reviews && boat.reviews.length > 0" class="space-y-4">
              <div v-for="review in boat.reviews" :key="review.id" class="border-b border-gray-100 pb-4 last:border-0">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                      <span class="text-blue-600 font-semibold text-sm">{{ review.user?.name?.charAt(0) || '?' }}</span>
                    </div>
                    <span class="font-medium text-gray-900">{{ review.user?.name || 'Anonymous' }}</span>
                  </div>
                  <div class="flex items-center">
                    <span v-for="i in 5" :key="i" :class="i <= review.rating ? 'text-yellow-500' : 'text-gray-300'">&#9733;</span>
                  </div>
                </div>
                <p class="text-gray-600 text-sm">{{ review.comment }}</p>
              </div>
            </div>
            <p v-else class="text-gray-400 text-center py-4">No reviews yet</p>
          </div>
        </div>

        <div class="lg:col-span-1">
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-6">
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-medium text-blue-600 bg-blue-50 px-3 py-1 rounded-full capitalize">{{ boat.type }}</span>
              <div class="flex items-center text-sm">
                <span class="text-yellow-500">&#9733;</span>
                <span class="ml-1 font-medium">{{ boat.average_rating }}</span>
              </div>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ boat.name }}</h1>
            <p class="text-gray-500 mb-4">{{ boat.location }}</p>

            <div class="border-t border-gray-100 pt-4 space-y-3">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Capacity</span>
                <span class="font-medium">{{ boat.capacity }} guests</span>
              </div>
              <div v-if="boat.manufacturer" class="flex justify-between text-sm">
                <span class="text-gray-500">Manufacturer</span>
                <span class="font-medium">{{ boat.manufacturer }}</span>
              </div>
              <div v-if="boat.year_built" class="flex justify-between text-sm">
                <span class="text-gray-500">Year Built</span>
                <span class="font-medium">{{ boat.year_built }}</span>
              </div>
              <div v-if="boat.length_ft" class="flex justify-between text-sm">
                <span class="text-gray-500">Length</span>
                <span class="font-medium">{{ boat.length_ft }} ft</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Owner</span>
                <span class="font-medium">{{ boat.owner?.name || 'N/A' }}</span>
              </div>
            </div>

            <div class="border-t border-gray-100 mt-4 pt-4">
              <div class="flex items-baseline justify-between mb-1">
                <span class="text-3xl font-bold text-blue-600">${{ boat.price_per_hour }}</span>
                <span class="text-gray-400">/hour</span>
              </div>
              <div class="flex items-baseline justify-between">
                <span class="text-xl font-semibold text-gray-700">${{ boat.price_per_day }}</span>
                <span class="text-gray-400">/day</span>
              </div>
            </div>

            <router-link :to="`/book/${boat.slug}`" class="block w-full bg-blue-600 text-white text-center py-3 rounded-lg font-semibold hover:bg-blue-700 transition mt-6">
              Book Now
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-16">
      <h2 class="text-xl font-semibold text-gray-600">Boat not found</h2>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';

const props = defineProps({ slug: String });
const boat = ref(null);
const loading = ref(true);

onMounted(async () => {
  try {
    const response = await api.get(`/boats/${props.slug}`);
    boat.value = response.data;
  } catch (e) {
    console.error('Failed to load boat:', e);
  } finally {
    loading.value = false;
  }
});
</script>
