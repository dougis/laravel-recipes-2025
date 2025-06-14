<?php

namespace Tests\Unit\Models;

use App\Models\Preparation;
use App\Models\Recipe;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PreparationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test preparation model basic attributes.
     */
    public function test_preparation_model_attributes()
    {
        $preparation = Preparation::create([
            'name' => 'Baked',
        ]);

        $this->assertInstanceOf(Preparation::class, $preparation);
        $this->assertEquals('Baked', $preparation->name);
    }

    /**
     * Test fillable attributes.
     */
    public function test_fillable_attributes()
    {
        $fillable = ['name'];

        $preparation = new Preparation();
        $this->assertEquals($fillable, $preparation->getFillable());
    }

    /**
     * Test recipes relationship.
     */
    public function test_recipes_relationship()
    {
        $preparation = new Preparation();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $preparation->recipes());
    }

    /**
     * Test MongoDB connection.
     */
    public function test_mongodb_connection()
    {
        $preparation = new Preparation();
        $this->assertEquals('mongodb', $preparation->getConnectionName());
        $this->assertEquals('preparations', $preparation->getTable());
    }

    /**
     * Test preparation creation.
     */
    public function test_preparation_creation()
    {
        $preparation = Preparation::create(['name' => 'Grilled']);
        
        $this->assertDatabaseHas('preparations', [
            'name' => 'Grilled'
        ]);
    }

    /**
     * Test multiple preparation types.
     */
    public function test_multiple_preparation_types()
    {
        $baked = Preparation::create(['name' => 'Baked']);
        $grilled = Preparation::create(['name' => 'Grilled']);
        $fried = Preparation::create(['name' => 'Fried']);
        $steamed = Preparation::create(['name' => 'Steamed']);
        $roasted = Preparation::create(['name' => 'Roasted']);

        $this->assertEquals('Baked', $baked->name);
        $this->assertEquals('Grilled', $grilled->name);
        $this->assertEquals('Fried', $fried->name);
        $this->assertEquals('Steamed', $steamed->name);
        $this->assertEquals('Roasted', $roasted->name);
    }

    /**
     * Test HasFactory trait.
     */
    public function test_has_factory_trait()
    {
        $preparation = new Preparation();
        $this->assertTrue(method_exists($preparation, 'factory'));
    }
}
