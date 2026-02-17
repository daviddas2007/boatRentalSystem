<?php

namespace Database\Seeders;

use App\Models\Boat;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@boatrental.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '+1-555-0100',
        ]);

        $customers = [];
        $customerData = [
            ['name' => 'John Smith', 'email' => 'john@example.com'],
            ['name' => 'Jane Doe', 'email' => 'jane@example.com'],
            ['name' => 'Mike Johnson', 'email' => 'mike@example.com'],
            ['name' => 'Sarah Williams', 'email' => 'sarah@example.com'],
            ['name' => 'David Brown', 'email' => 'david@example.com'],
        ];

        foreach ($customerData as $data) {
            $customers[] = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password123'),
                'role' => 'customer',
            ]);
        }

        $boatsData = [
            [
                'name' => 'Ocean Breeze',
                'type' => 'yacht',
                'description' => 'A luxurious 45-foot yacht perfect for sunset cruises and special occasions. Features a spacious deck, modern interior, and full kitchen.',
                'capacity' => 12,
                'price_per_hour' => 250.00,
                'price_per_day' => 1500.00,
                'location' => 'Miami Marina, FL',
                'amenities' => ['WiFi', 'GPS', 'Kitchen', 'Bathroom', 'Sound System', 'Air Conditioning'],
                'manufacturer' => 'Azimut',
                'year_built' => 2020,
                'length_ft' => 45,
            ],
            [
                'name' => 'Wind Dancer',
                'type' => 'sailboat',
                'description' => 'A beautiful 32-foot sailboat ideal for experienced sailors looking for an authentic sailing experience on the open waters.',
                'capacity' => 6,
                'price_per_hour' => 120.00,
                'price_per_day' => 700.00,
                'location' => 'San Diego Harbor, CA',
                'amenities' => ['GPS', 'Life Jackets', 'First Aid Kit', 'Cooler'],
                'manufacturer' => 'Beneteau',
                'year_built' => 2019,
                'length_ft' => 32,
            ],
            [
                'name' => 'Thunder Strike',
                'type' => 'speedboat',
                'description' => 'A high-performance speedboat for thrill-seekers. Perfect for water sports, tubing, and wakeboarding.',
                'capacity' => 8,
                'price_per_hour' => 180.00,
                'price_per_day' => 1000.00,
                'location' => 'Lake Tahoe, NV',
                'amenities' => ['GPS', 'Bluetooth Speakers', 'Wakeboard Rack', 'Cooler', 'Swim Platform'],
                'manufacturer' => 'MasterCraft',
                'year_built' => 2022,
                'length_ft' => 24,
            ],
            [
                'name' => 'Serenity Explorer',
                'type' => 'kayak',
                'description' => 'A tandem kayak perfect for exploring calm waters, coves, and coastlines. Great for beginners and families.',
                'capacity' => 2,
                'price_per_hour' => 25.00,
                'price_per_day' => 80.00,
                'location' => 'Key West, FL',
                'amenities' => ['Life Jackets', 'Dry Bag', 'Paddle'],
                'manufacturer' => 'Ocean Kayak',
                'year_built' => 2023,
                'length_ft' => 12,
            ],
            [
                'name' => 'Sunset Cruiser',
                'type' => 'pontoon',
                'description' => 'A spacious pontoon boat perfect for family outings, fishing trips, and relaxed lake cruises.',
                'capacity' => 14,
                'price_per_hour' => 150.00,
                'price_per_day' => 850.00,
                'location' => 'Lake Michigan, MI',
                'amenities' => ['Bimini Top', 'GPS', 'Fish Finder', 'Bluetooth Speakers', 'Cooler', 'Grill'],
                'manufacturer' => 'Sun Tracker',
                'year_built' => 2021,
                'length_ft' => 26,
            ],
            [
                'name' => 'Blue Horizon',
                'type' => 'catamaran',
                'description' => 'A stunning 40-foot catamaran offering stability, space, and comfort for multi-day sailing adventures.',
                'capacity' => 10,
                'price_per_hour' => 200.00,
                'price_per_day' => 1200.00,
                'location' => 'Honolulu, HI',
                'amenities' => ['WiFi', 'Kitchen', 'Cabins', 'Snorkeling Gear', 'Kayak', 'BBQ'],
                'manufacturer' => 'Lagoon',
                'year_built' => 2021,
                'length_ft' => 40,
            ],
            [
                'name' => 'Reef Runner',
                'type' => 'speedboat',
                'description' => 'A versatile center console speedboat ideal for fishing, diving, and island hopping.',
                'capacity' => 6,
                'price_per_hour' => 160.00,
                'price_per_day' => 900.00,
                'location' => 'Tampa Bay, FL',
                'amenities' => ['GPS', 'Fish Finder', 'Live Well', 'Rod Holders', 'T-Top'],
                'manufacturer' => 'Boston Whaler',
                'year_built' => 2020,
                'length_ft' => 22,
            ],
            [
                'name' => 'Island Princess',
                'type' => 'yacht',
                'description' => 'An elegant 60-foot motor yacht with premium amenities. Perfect for corporate events and luxury getaways.',
                'capacity' => 20,
                'price_per_hour' => 500.00,
                'price_per_day' => 3000.00,
                'location' => 'Newport Beach, CA',
                'amenities' => ['WiFi', 'Full Kitchen', 'Master Suite', 'Hot Tub', 'Bar', 'Entertainment System'],
                'manufacturer' => 'Sunseeker',
                'year_built' => 2022,
                'length_ft' => 60,
            ],
        ];

        $boats = [];
        foreach ($boatsData as $data) {
            $data['owner_id'] = $admin->id;
            $boats[] = Boat::create($data);
        }

        $bookings = [];
        foreach ($customers as $i => $customer) {
            $boat = $boats[$i % count($boats)];
            $startDate = Carbon::now()->subDays(rand(1, 30));

            $booking = Booking::create([
                'user_id' => $customer->id,
                'boat_id' => $boat->id,
                'start_date' => $startDate,
                'end_date' => $startDate->copy()->addHours(4),
                'duration_type' => 'hourly',
                'duration_value' => 4,
                'total_price' => $boat->price_per_hour * 4,
                'status' => 'completed',
            ]);
            $bookings[] = $booking;

            Payment::create([
                'booking_id' => $booking->id,
                'user_id' => $customer->id,
                'amount' => $booking->total_price,
                'status' => 'succeeded',
                'stripe_payment_intent_id' => 'pi_demo_' . $booking->id,
            ]);

            $comments = [
                'Amazing experience! The boat was in perfect condition.',
                'Great time on the water. Would definitely book again.',
                'Fantastic boat and beautiful location. Highly recommend!',
                'Very enjoyable trip. The boat had everything we needed.',
                'Wonderful day out on the water. Five stars!',
            ];

            Review::create([
                'user_id' => $customer->id,
                'boat_id' => $boat->id,
                'booking_id' => $booking->id,
                'rating' => rand(4, 5),
                'comment' => $comments[$i % count($comments)],
            ]);
        }

        foreach ($customers as $i => $customer) {
            if ($i >= 3) break;
            $boat = $boats[($i + 3) % count($boats)];
            $startDate = Carbon::now()->addDays(rand(3, 14));

            $booking = Booking::create([
                'user_id' => $customer->id,
                'boat_id' => $boat->id,
                'start_date' => $startDate,
                'end_date' => $startDate->copy()->addDays(1),
                'duration_type' => 'daily',
                'duration_value' => 1,
                'total_price' => $boat->price_per_day,
                'status' => $i === 0 ? 'confirmed' : 'pending',
            ]);

            if ($i === 0) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'user_id' => $customer->id,
                    'amount' => $booking->total_price,
                    'status' => 'succeeded',
                    'stripe_payment_intent_id' => 'pi_demo_future_' . $booking->id,
                ]);
            }
        }
    }
}
