<template>
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
      <router-link :to="`/bookings/${id}`" class="text-blue-600 hover:underline text-sm">&larr; Back to booking</router-link>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="booking" class="space-y-6">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Complete Payment</h1>
        <p class="text-gray-600 text-sm mb-5">
          Booking #{{ booking.id }} for {{ booking.boat?.name }}
        </p>

        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-500">Booking Status</span>
            <span class="font-medium capitalize">{{ booking.status }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Amount Due</span>
            <span class="font-semibold text-lg text-blue-700">${{ Number(booking.total_price).toFixed(2) }}</span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div v-if="message" :class="messageClass" class="mb-4 p-3 rounded-lg text-sm">{{ message }}</div>

        <div v-if="isAlreadyPaid" class="space-y-3">
          <p class="text-green-700 font-medium">This booking is already paid.</p>
          <router-link :to="`/bookings/${id}`" class="inline-flex items-center bg-blue-600 text-white px-5 py-2.5 rounded-lg font-medium hover:bg-blue-700 transition">
            View Booking
          </router-link>
        </div>

        <div v-else>
          <label class="block text-sm font-medium text-gray-700 mb-2">Card Details</label>
          <div id="card-element" class="w-full px-4 py-3 border border-gray-300 rounded-lg"></div>
          <p class="text-xs text-gray-500 mt-2">Use Stripe test card: 4242 4242 4242 4242</p>

          <button
            @click="payNow"
            :disabled="submitting || !stripeReady || !clientSecret"
            class="mt-5 w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50"
          >
            {{ submitting ? 'Processing Payment...' : `Pay $${Number(booking.total_price).toFixed(2)}` }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';
import api from '../../services/api';

const props = defineProps({ id: [String, Number] });

const id = props.id;
const booking = ref(null);
const loading = ref(true);
const submitting = ref(false);
const message = ref('');
const messageType = ref('error');

const clientSecret = ref('');
const stripeReady = ref(false);

let stripe = null;
let elements = null;
let cardElement = null;

const isAlreadyPaid = computed(() => {
  return booking.value?.payment?.status === 'succeeded';
});

const messageClass = computed(() => {
  return messageType.value === 'success'
    ? 'bg-green-50 text-green-700'
    : 'bg-red-50 text-red-700';
});

const setMessage = (text, type = 'error') => {
  message.value = text;
  messageType.value = type;
};

const loadBooking = async () => {
  const response = await api.get(`/bookings/${id}`);
  booking.value = response.data;
};

const createIntent = async () => {
  const response = await api.post('/payments/create-intent', {
    booking_id: booking.value.id,
  });
  clientSecret.value = response.data.client_secret;
};

const initStripeCard = () => {
  if (!window.Stripe) {
    setMessage('Stripe SDK failed to load. Please refresh and try again.');
    return;
  }

  const publishableKey = document
    .querySelector('meta[name="stripe-publishable-key"]')
    ?.getAttribute('content');

  if (!publishableKey) {
    setMessage('Stripe publishable key is missing.');
    return;
  }

  stripe = window.Stripe(publishableKey);
  elements = stripe.elements();
  cardElement = elements.create('card', {
    hidePostalCode: true,
    style: {
      base: {
        color: '#111827',
        fontSize: '16px',
      },
    },
  });
  cardElement.mount('#card-element');
  stripeReady.value = true;
};

const payNow = async () => {
  if (!stripe || !cardElement || !clientSecret.value) {
    setMessage('Payment form is not ready yet.');
    return;
  }

  submitting.value = true;
  setMessage('');

  try {
    const result = await stripe.confirmCardPayment(clientSecret.value, {
      payment_method: {
        card: cardElement,
      },
    });

    if (result.error) {
      setMessage(result.error.message || 'Payment failed.');
      return;
    }

    if (result.paymentIntent?.id) {
      await api.post('/payments/confirm', {
        payment_intent_id: result.paymentIntent.id,
      });
    }

    await loadBooking();
    setMessage('Payment successful. Your booking is now confirmed.', 'success');
  } catch (e) {
    setMessage(e.response?.data?.message || 'Unable to process payment right now.');
  } finally {
    submitting.value = false;
  }
};

onMounted(async () => {
  try {
    await loadBooking();

    if (isAlreadyPaid.value) {
      loading.value = false;
      return;
    }

    if (booking.value.status !== 'pending') {
      setMessage('Only pending bookings can be paid from this page.');
      loading.value = false;
      return;
    }

    await createIntent();
    loading.value = false;
    await nextTick();
    initStripeCard();
  } catch (e) {
    setMessage(e.response?.data?.message || 'Failed to load payment page.');
    loading.value = false;
  } finally {
    // loading is explicitly controlled above to ensure Stripe mounts after DOM render.
  }
});
</script>
