<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use Faker\Factory as Faker;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeder for students in Kenya.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        
        // Kenyan-specific data
        $kenyanNames = [
            'Wanjiku Kamau', 'John Mwangi', 'Grace Achieng', 'Peter Kiprotich', 'Mary Wanjiru',
            'David Otieno', 'Faith Nyambura', 'Samuel Kipchoge', 'Lucy Wangari', 'Michael Ochieng',
            'Sarah Njeri', 'James Mutua', 'Catherine Akinyi', 'Daniel Kiptoo', 'Rose Wairimu'
        ];
        
        $classes = ['PP1', 'PP2', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12', 'Grade 13'];
        $streams = ['A', 'B', 'C', 'D', 'East', 'West', 'North', 'South', 'Red', 'Blue']; // More varied stream options
        $houses = ['Kenyatta House', 'Nyerere House', 'Mandela House', 'Nkrumah House'];
        $kenyanCities = ['Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret', 'Thika', 'Machakos', 'Meru'];
        
        for ($i = 1; $i <= 50; $i++) {
            $name = $faker->randomElement($kenyanNames);
            $class = $faker->randomElement($classes);
            $stream = $faker->randomElement($streams);
            
            Client::create([
                'name' => $name,
                'code' => 1000 + $i,
                'student_id' => 'STU' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'admission_number' => 'ADM' . date('Y') . str_pad($i, 3, '0', STR_PAD_LEFT),
                'class_grade' => $class,
                'stream' => $stream,
                'house' => $faker->randomElement($houses),
                'date_of_birth' => $faker->dateTimeBetween('-20 years', '-14 years')->format('Y-m-d'),
                'gender' => $faker->randomElement(['Male', 'Female']),
                'phone' => '+254' . $faker->numberBetween(700000000, 799999999),
                'email' => strtolower(str_replace(' ', '.', $name)) . '@student.school.ke',
                'country' => 'Kenya',
                'city' => $faker->randomElement($kenyanCities),
                'adresse' => $faker->address,
                'parent_guardian_name' => $faker->name,
                'parent_guardian_phone' => '+254' . $faker->numberBetween(700000000, 799999999),
                'parent_guardian_email' => $faker->email,
                'emergency_contact' => 'Emergency: ' . $faker->name . ' - +254' . $faker->numberBetween(700000000, 799999999),
                'account_balance' => $faker->randomFloat(2, 0, 5000), // 0 to 5000 KES
                'credit_limit' => $faker->randomFloat(2, 1000, 10000), // 1000 to 10000 KES
                'status' => $faker->randomElement(['Active', 'Active', 'Active', 'Active', 'Inactive']), // 80% active
                'notes' => $faker->optional(0.3)->sentence(), // 30% chance of having notes
                'tax_number' => null, // Students don't need tax numbers
            ]);
        }
    }
}