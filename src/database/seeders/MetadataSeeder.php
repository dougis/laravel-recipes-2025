<?php

namespace Database\Seeders;

use App\Models\Classification;
use App\Models\Course;
use App\Models\Meal;
use App\Models\Preparation;
use App\Models\Source;
use Illuminate\Database\Seeder;

class MetadataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed classifications
        $classifications = [
            'Appetizer', 'Breakfast', 'Dessert', 'Main Dish', 'Salad',
            'Side Dish', 'Soup', 'Beverage', 'Bread', 'Snack',
        ];

        foreach ($classifications as $name) {
            Classification::create(['name' => $name]);
        }

        // Seed sources
        $sources = [
            'Family Recipe', 'Magazine', 'Internet', 'Cookbook', 'Friend',
            'Original Creation', 'Restaurant', 'TV Show', 'Cooking Class',
        ];

        foreach ($sources as $name) {
            Source::create(['name' => $name]);
        }

        // Seed meals
        $meals = [
            'Breakfast', 'Brunch', 'Lunch', 'Dinner', 'Snack', 'Dessert',
        ];

        foreach ($meals as $name) {
            Meal::create(['name' => $name]);
        }

        // Seed courses
        $courses = [
            'Appetizer', 'Soup', 'Salad', 'Main Course', 'Side Dish',
            'Dessert', 'Beverage', 'Bread',
        ];

        foreach ($courses as $name) {
            Course::create(['name' => $name]);
        }

        // Seed preparation methods
        $preparations = [
            'Bake', 'Boil', 'Broil', 'Fry', 'Grill', 'Roast', 'Saute',
            'Slow Cook', 'Steam', 'No Cook', 'Microwave', 'Poach',
        ];

        foreach ($preparations as $name) {
            Preparation::create(['name' => $name]);
        }
    }
}
