<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Property;
use App\Models\Availability;
use Illuminate\Support\Facades\DB;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example properties
        $properties = [
            [
                'title' => 'Luxury Beach Villa',
                'description' => 'A beautiful villa with ocean views and private pool.',
                'price_per_night' => 250.00,
                'location' => 'Malibu, California',
                'amenities' => json_encode(['pool', 'wifi', 'parking', 'air conditioning']),
                'images' => json_encode([
                    'https://picsum.photos/200/300?grayscale',
                    'https://picsum.photos/seed/picsum/200/300',
                ]),
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'title' => 'Cozy Mountain Cabin',
                'description' => 'Perfect getaway in the mountains with fireplace.',
                'price_per_night' => 120.00,
                'location' => 'Aspen, Colorado',
                'amenities' => json_encode(['fireplace', 'wifi', 'kitchen']),
                'images' => json_encode([
                    'https://picsum.photos/id/237/200/300',
                    'https://picsum.photos/seed/picsum/200/300',
                ]),
                'created_by' => 1,
                'updated_by' => 1,
            ],
        ];

        foreach ($properties as $data) {
            $property = Property::create($data);

            // Add some availabilities
            Availability::create([
                'property_id' => $property->id,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(15)->toDateString(),
            ]);

            Availability::create([
                'property_id' => $property->id,
                'start_date' => now()->addDays(20)->toDateString(),
                'end_date' => now()->addDays(30)->toDateString(),
            ]);
        }
    }
}
