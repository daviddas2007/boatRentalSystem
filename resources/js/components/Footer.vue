<template>
  <footer class="bg-gray-900 text-white mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <div>
          <h3 class="text-lg font-bold mb-4">BoatRental</h3>
          <p class="text-gray-400 text-sm">Your premier destination for boat rentals. Explore the waters with our wide selection of boats.</p>
        </div>
        <div>
          <h4 class="font-semibold mb-4">Quick Links</h4>
          <ul class="space-y-2 text-gray-400 text-sm">
            <li><router-link to="/boats" class="hover:text-white transition">Browse Boats</router-link></li>
            <li><router-link to="/register" class="hover:text-white transition">Sign Up</router-link></li>
            <li><router-link to="/login" class="hover:text-white transition">Login</router-link></li>
          </ul>
        </div>
        <div>
          <h4 class="font-semibold mb-4">Boat Types</h4>
          <ul class="space-y-2 text-gray-400 text-sm">
            <li>Sailboats</li>
            <li>Yachts</li>
            <li>Speedboats</li>
            <li>Kayaks</li>
          </ul>
        </div>
        <div>
          <h4 class="font-semibold mb-4">Contact</h4>
          <ul class="space-y-2 text-gray-400 text-sm">
            <li>support@boatrental.com</li>
            <li>1-800-BOAT-RENT</li>
            <li>123 Marina Way, Harbor City</li>
          </ul>
        </div>
      </div>
      <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-500 text-sm">
        &copy; {{ new Date().getFullYear() }} BoatRental. All rights reserved.
      </div>
    </div>
  </footer>
</template>
