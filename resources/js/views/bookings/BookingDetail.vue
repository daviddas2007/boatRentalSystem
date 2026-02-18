<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
      <router-link to="/my-bookings" class="text-blue-600 hover:underline text-sm">&larr; Back to bookings</router-link>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="booking">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Booking #{{ booking.id }}</h1>
          <span :class="statusClass(booking.status)" class="px-4 py-1.5 rounded-full text-sm font-semibold capitalize">
            {{ booking.status }}
          </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h2 class="font-semibold text-gray-900 mb-3">Boat Details</h2>
            <div class="flex items-center mb-3">
              <div class="w-20 h-16 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                <img v-if="booking.boat?.featured_image" :src="`/storage/${booking.boat.featured_image}`" class="w-full h-full object-cover rounded-lg" />
                <span v-else class="text-2xl">&#9973;</span>
              </div>
              <div>
                <router-link :to="`/boats/${booking.boat?.slug}`" class="font-bold text-blue-600 hover:underline">{{ booking.boat?.name }}</router-link>
                <p class="text-gray-500 text-sm capitalize">{{ booking.boat?.type }}</p>
              </div>
            </div>
          </div>

          <div>
            <h2 class="font-semibold text-gray-900 mb-3">Schedule</h2>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500">Start</span>
                <span class="font-medium">{{ new Date(booking.start_date).toLocaleString() }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">End</span>
                <span class="font-medium">{{ new Date(booking.end_date).toLocaleString() }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Duration</span>
                <span class="font-medium">{{ booking.duration_value }} {{ booking.duration_type === 'hourly' ? 'hour(s)' : 'day(s)' }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="border-t border-gray-100 mt-6 pt-6">
          <div class="flex items-center justify-between">
            <span class="text-lg font-semibold text-gray-900">Total Price</span>
            <span class="text-2xl font-bold text-blue-600">${{ booking.total_price }}</span>
          </div>
        </div>

        <div v-if="booking.notes" class="border-t border-gray-100 mt-4 pt-4">
          <h3 class="font-semibold text-gray-900 mb-1">Notes</h3>
          <p class="text-gray-600 text-sm">{{ booking.notes }}</p>
        </div>
      </div>

      <div v-if="booking.payment" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Payment</h2>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-500">Amount</span>
            <span class="font-medium">${{ booking.payment.amount }} {{ booking.payment.currency }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Status</span>
            <span class="font-medium capitalize">{{ booking.payment.status }}</span>
          </div>
          <div v-if="booking.payment.refund_amount" class="flex justify-between">
            <span class="text-gray-500">Refund</span>
            <span class="font-medium text-red-600">-${{ booking.payment.refund_amount }}</span>
          </div>
        </div>
      </div>

      <div class="flex space-x-4">
        <router-link
          v-if="canPayNow"
          :to="`/bookings/${booking.id}/payment`"
          class="bg-blue-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-blue-700 transition"
        >
          Pay Now
        </router-link>

        <button v-if="booking.status === 'pending' || booking.status === 'confirmed'" @click="cancelBooking" :disabled="cancelling" class="bg-red-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-red-700 transition disabled:opacity-50">
          {{ cancelling ? 'Cancelling...' : 'Cancel Booking' }}
        </button>

        <button v-if="booking.status === 'completed' && !booking.review" @click="showReviewForm = true" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-blue-700 transition">
          Leave Review
        </button>
      </div>

      <div v-if="booking.review" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
        <h2 class="text-lg font-bold text-gray-900 mb-3">Your Review</h2>
        <div class="flex items-center mb-2">
          <span v-for="i in 5" :key="i" :class="i <= booking.review.rating ? 'text-yellow-500' : 'text-gray-300'" class="text-lg">&#9733;</span>
        </div>
        <p class="text-gray-600">{{ booking.review.comment }}</p>
      </div>

      <div v-if="showReviewForm" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Write a Review</h2>
        <form @submit.prevent="submitReview">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
            <div class="flex space-x-1">
              <button v-for="i in 5" :key="i" type="button" @click="reviewForm.rating = i" :class="i <= reviewForm.rating ? 'text-yellow-500' : 'text-gray-300'" class="text-3xl hover:text-yellow-400 transition">
                &#9733;
              </button>
            </div>
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
            <textarea v-model="reviewForm.comment" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Share your experience..."></textarea>
          </div>
          <div class="flex space-x-3">
            <button type="submit" :disabled="submittingReview" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-blue-700 transition disabled:opacity-50">
              {{ submittingReview ? 'Submitting...' : 'Submit Review' }}
            </button>
            <button type="button" @click="showReviewForm = false" class="border border-gray-300 text-gray-600 px-6 py-2.5 rounded-lg font-medium hover:bg-gray-50 transition">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import api from '../../services/api';

const props = defineProps({ id: [String, Number] });
const booking = ref(null);
const loading = ref(true);
const cancelling = ref(false);
const showReviewForm = ref(false);
const submittingReview = ref(false);

const reviewForm = reactive({ rating: 5, comment: '' });

const canPayNow = computed(() => {
  if (!booking.value) return false;
  return booking.value.status === 'pending' && booking.value.payment?.status !== 'succeeded';
});

const statusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
    completed: 'bg-blue-100 text-blue-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const loadBooking = async () => {
  try {
    const response = await api.get(`/bookings/${props.id}`);
    booking.value = response.data;
  } catch (e) {
    console.error('Failed to load booking:', e);
  } finally {
    loading.value = false;
  }
};

const cancelBooking = async () => {
  if (!confirm('Are you sure you want to cancel this booking?')) return;
  cancelling.value = true;
  try {
    await api.post(`/bookings/${props.id}/cancel`);
    await loadBooking();
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to cancel booking');
  } finally {
    cancelling.value = false;
  }
};

const submitReview = async () => {
  submittingReview.value = true;
  try {
    await api.post('/reviews', {
      booking_id: booking.value.id,
      rating: reviewForm.rating,
      comment: reviewForm.comment,
    });
    showReviewForm.value = false;
    await loadBooking();
  } catch (e) {
    alert(e.response?.data?.message || 'Failed to submit review');
  } finally {
    submittingReview.value = false;
  }
};

onMounted(loadBooking);
</script>
