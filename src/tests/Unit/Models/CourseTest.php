<?php

namespace Tests\Unit\Models;

use App\Models\Course;
use App\Models\Recipe;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test course model basic attributes.
     */
    public function test_course_model_attributes()
    {
        $course = Course::create([
            'name' => 'Appetizer',
        ]);

        $this->assertInstanceOf(Course::class, $course);
        $this->assertEquals('Appetizer', $course->name);
    }

    /**
     * Test fillable attributes.
     */
    public function test_fillable_attributes()
    {
        $fillable = ['name'];

        $course = new Course();
        $this->assertEquals($fillable, $course->getFillable());
    }

    /**
     * Test recipes relationship.
     */
    public function test_recipes_relationship()
    {
        $course = new Course();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $course->recipes());
    }

    /**
     * Test MongoDB connection.
     */
    public function test_mongodb_connection()
    {
        $course = new Course();
        $this->assertEquals('mongodb', $course->getConnectionName());
        $this->assertEquals('courses', $course->getTable());
    }

    /**
     * Test course creation.
     */
    public function test_course_creation()
    {
        $course = Course::create(['name' => 'Main Course']);
        
        $this->assertDatabaseHas('courses', [
            'name' => 'Main Course'
        ]);
    }

    /**
     * Test multiple course types.
     */
    public function test_multiple_course_types()
    {
        $appetizer = Course::create(['name' => 'Appetizer']);
        $main = Course::create(['name' => 'Main Course']);
        $dessert = Course::create(['name' => 'Dessert']);
        $side = Course::create(['name' => 'Side Dish']);

        $this->assertEquals('Appetizer', $appetizer->name);
        $this->assertEquals('Main Course', $main->name);
        $this->assertEquals('Dessert', $dessert->name);
        $this->assertEquals('Side Dish', $side->name);
    }

    /**
     * Test HasFactory trait.
     */
    public function test_has_factory_trait()
    {
        $course = new Course();
        $this->assertTrue(method_exists($course, 'factory'));
    }
}
