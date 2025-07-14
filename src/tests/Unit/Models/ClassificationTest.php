<?php

namespace Tests\Unit\Models;

use App\Models\Classification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test classification model basic attributes.
     */
    public function test_classification_model_attributes()
    {
        $classification = Classification::create([
            'name' => 'Italian',
        ]);

        $this->assertInstanceOf(Classification::class, $classification);
        $this->assertEquals('Italian', $classification->name);
    }

    /**
     * Test fillable attributes.
     */
    public function test_fillable_attributes()
    {
        $fillable = ['name'];

        $classification = new Classification;
        $this->assertEquals($fillable, $classification->getFillable());
    }

    /**
     * Test recipes relationship.
     */
    public function test_recipes_relationship()
    {
        $classification = new Classification;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $classification->recipes());
    }

    /**
     * Test MongoDB connection.
     */
    public function test_mongodb_connection()
    {
        $classification = new Classification;
        $this->assertEquals('mongodb', $classification->getConnectionName());
        $this->assertEquals('classifications', $classification->getTable());
    }

    /**
     * Test classification name validation.
     */
    public function test_classification_creation()
    {
        $classification = Classification::create(['name' => 'Mexican']);

        $this->assertDatabaseHas('classifications', [
            'name' => 'Mexican',
        ]);
    }

    /**
     * Test HasFactory trait.
     */
    public function test_has_factory_trait()
    {
        $classification = new Classification;
        $this->assertTrue(method_exists($classification, 'factory'));
    }
}
