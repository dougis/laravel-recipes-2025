<?php

namespace Tests\Unit\Models;

use App\Models\Meal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MealTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test meal model basic attributes.
     */
    public function test_meal_model_attributes()
    {
        $meal = Meal::create([
            'name' => 'Breakfast',
        ]);

        $this->assertInstanceOf(Meal::class, $meal);
        $this->assertEquals('Breakfast', $meal->name);
    }

    /**
     * Test fillable attributes.
     */
    public function test_fillable_attributes()
    {
        $fillable = ['name'];

        $meal = new Meal;
        $this->assertEquals($fillable, $meal->getFillable());
    }

    /**
     * Test recipes relationship.
     */
    public function test_recipes_relationship()
    {
        $meal = new Meal;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $meal->recipes());
    }

    /**
     * Test MongoDB connection.
     */
    public function test_mongodb_connection()
    {
        $meal = new Meal;
        $this->assertEquals('mongodb', $meal->getConnectionName());
        $this->assertEquals('meals', $meal->getTable());
    }

    /**
     * Test meal creation.
     */
    public function test_meal_creation()
    {
        $meal = Meal::create(['name' => 'Lunch']);

        $this->assertDatabaseHas('meals', [
            'name' => 'Lunch',
        ]);
    }

    /**
     * Test multiple meal types.
     */
    public function test_multiple_meal_types()
    {
        $breakfast = Meal::create(['name' => 'Breakfast']);
        $lunch = Meal::create(['name' => 'Lunch']);
        $dinner = Meal::create(['name' => 'Dinner']);
        $snack = Meal::create(['name' => 'Snack']);

        $this->assertEquals('Breakfast', $breakfast->name);
        $this->assertEquals('Lunch', $lunch->name);
        $this->assertEquals('Dinner', $dinner->name);
        $this->assertEquals('Snack', $snack->name);
    }

    /**
     * Test HasFactory trait.
     */
    public function test_has_factory_trait()
    {
        $meal = new Meal;
        $this->assertTrue(method_exists($meal, 'factory'));
    }
}
