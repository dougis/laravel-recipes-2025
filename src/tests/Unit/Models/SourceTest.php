<?php

namespace Tests\Unit\Models;

use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test source model basic attributes.
     */
    public function test_source_model_attributes()
    {
        $source = Source::create([
            'name' => 'Grandma\'s Cookbook',
        ]);

        $this->assertInstanceOf(Source::class, $source);
        $this->assertEquals('Grandma\'s Cookbook', $source->name);
    }

    /**
     * Test fillable attributes.
     */
    public function test_fillable_attributes()
    {
        $fillable = ['name'];

        $source = new Source;
        $this->assertEquals($fillable, $source->getFillable());
    }

    /**
     * Test recipes relationship.
     */
    public function test_recipes_relationship()
    {
        $source = new Source;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $source->recipes());
    }

    /**
     * Test MongoDB connection.
     */
    public function test_mongodb_connection()
    {
        $source = new Source;
        $this->assertEquals('mongodb', $source->getConnectionName());
        $this->assertEquals('sources', $source->getTable());
    }

    /**
     * Test source creation.
     */
    public function test_source_creation()
    {
        $source = Source::create(['name' => 'Food Network']);

        $this->assertDatabaseHas('sources', [
            'name' => 'Food Network',
        ]);
    }

    /**
     * Test HasFactory trait.
     */
    public function test_has_factory_trait()
    {
        $source = new Source;
        $this->assertTrue(method_exists($source, 'factory'));
    }
}
