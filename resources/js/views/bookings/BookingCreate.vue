<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
      <router-link :to="`/boats/${slug}`" class="text-blue-600 hover:underline text-sm">&larr; Back to boat</router-link>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="boat" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Book {{ boat.name }}</h1>

        <form @submit.prevent="handleBooking" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div v-if="error" class="bg-red-50 text-red-600 p-3 rounded-lg mb-4 text-sm">{{ error }}</div>
          <div v-if="success" class="bg-green-50 text-green-600 p-3 rounded-lg mb-4 text-sm">{{ success }}</div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date & Time</label>
            <input v-model="form.start_date" type="datetime-local" required :min="minDate" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" />
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Duration Type</label>
            <div class="grid grid-cols-2 gap-3">
              <button type="button" @click="form.duration_type = 'hourly'" :class="['px-4 py-3 rounded-lg border-2 text-center font-medium transition', form.duration_type === 'hourly' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 hover:border-gray-300']">
                Hourly (${{ boat.price_per_hour }}/hr)
              </button>
              <button type="button" @click="form.duration_type = 'daily'" :class="['px-4 py-3 rounded-lg border-2 text-center font-medium transition', form.duration_type === 'daily' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 hover:border-gray-300']">
                Daily (${{ boat.price_per_day }}/day)
              </button>
            </div>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Duration ({{ form.duration_type === 'hourly' ? 'hours' : 'days' }})
            </label>
            <input v-model.number="form.duration_value" type="number" min="1" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" />
          </div>

          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
            <textarea v-model="form.notes" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Any special requests..."></textarea>
          </div>

          <button type="submit" :disabled="submitting" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50">
            {{ submitting ? 'Creating Booking...' : 'Confirm Booking' }}
          </button>
        </form>
      </div>

      <div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-6">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Booking Summary</h2>

          <div class="flex items-center mb-4 pb-4 border-b border-gray-100">
            <div class="w-20 h-16 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
              <img v-if="boat.featured_image" :src="`/storage/${boat.featured_image}`" :alt="boat.name" class="w-full h-full object-cover rounded-lg" />
              <span v-else class="text-2xl">&#9973;</span>
            </div>
            <div>
              <h3 class="font-semibold text-gray-900">{{ boat.name }}</h3>
              <p class="text-gray-500 text-sm capitalize">{{ boat.type }} &middot; {{ boat.location }}</p>
            </div>
          </div>

          <div class="space-y-3 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500">Rate</span>
              <span class="font-medium">
                ${{ form.duration_type === 'hourly' ? boat.price_per_hour : boat.price_per_day }}
                / {{ form.duration_type === 'hourly' ? 'hour' : 'day' }}
              </span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Duration</span>
              <span class="font-medium">{{ form.duration_value }} {{ form.duration_type === 'hourly' ? 'hour(s)' : 'day(s)' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Start</span>
              <span class="font-medium">{{ form.start_date ? new Date(form.start_date).toLocaleString() : 'Not selected' }}</span>
            </div>
          </div>

          <div class="border-t border-gray-100 mt-4 pt-4">
            <div class="flex justify-between items-center">
              <span class="text-lg font-bold text-gray-900">Total</span>
              <span class="text-2xl font-bold text-blue-600">${{ totalPrice.toFixed(2) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';

const props = defineProps({ slug: String });
const router = useRouter();
const boat = ref(null);
const loading = ref(true);
const submitting = ref(false);
const error = ref('');
const success = ref('');

const form = reactive({
  start_date: '',
  duration_type: 'hourly',
  duration_value: 1,
  notes: '',
});

const minDate = computed(() => {
  const now = new Date();
  now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
  return now.toISOString().slice(0, 16);
});

const totalPrice = computed(() => {
  if (!boat.value) return 0;
  const rate = form.duration_type === 'hourly' ? parseFloat(boat.value.price_per_hour) : parseFloat(boat.value.price_per_day);
  return rate * form.duration_value;
});

const handleBooking = async () => {
  submitting.value = true;
  error.value = '';
  success.value = '';
  try {
    const response = await api.post('/bookings', {
      boat_id: boat.value.id,
      start_date: form.start_date,
      duration_type: form.duration_type,
      duration_value: form.duration_value,
      notes: form.notes,
    });
    success.value = 'Booking created successfully!';
    setTimeout(() => router.push(`/bookings/${response.data.id}`), 1500);
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to create booking.';
  } finally {
    submitting.value = false;
  }
};

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
