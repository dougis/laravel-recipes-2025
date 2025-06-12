<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PDFService;
use App\Models\Recipe;
use App\Models\Cookbook;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;
use Mockery;

class PDFServiceTest extends TestCase
{
    protected $pdfService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->pdfService = new PDFService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_generate_recipe_pdf_loads_relationships_and_creates_pdf()
    {
        // Arrange
        $recipe = Mockery::mock(Recipe::class);
        $mockPdf = Mockery::mock(DomPDF::class);
        
        $recipe->shouldReceive('load')
            ->once()
            ->with(['source', 'classification', 'meals', 'courses', 'preparations'])
            ->andReturnSelf();

        Pdf::shouldReceive('loadView')
            ->once()
            ->with('pdfs.recipe', Mockery::on(function($data) use ($recipe) {
                return isset($data['recipe']) && $data['recipe'] === $recipe;
            }))
            ->andReturn($mockPdf);

        $mockPdf->shouldReceive('setPaper')
            ->once()
            ->with('a4', 'portrait')
            ->andReturnSelf();

        // Act
        $result = $this->pdfService->generateRecipePDF($recipe);

        // Assert
        $this->assertEquals($mockPdf, $result);
    }

    public function test_generate_cookbook_pdf_processes_recipes_correctly()
    {
        // Arrange
        $cookbook = new Cookbook([
            '_id' => 'cookbook123',
            'name' => 'Test Cookbook',
            'recipe_ids' => [
                ['recipe_id' => 'recipe1', 'order' => 1],
                ['recipe_id' => 'recipe2', 'order' => 0],
                ['recipe_id' => 'recipe3', 'order' => 2]
            ]
        ]);

        $recipes = collect([
            new Recipe(['_id' => 'recipe1', 'name' => 'Recipe 1']),
            new Recipe(['_id' => 'recipe2', 'name' => 'Recipe 2']),
            new Recipe(['_id' => 'recipe3', 'name' => 'Recipe 3'])
        ]);

        // Mock Recipe::whereIn chain
        Recipe::shouldReceive('whereIn')
            ->once()
            ->with('_id', ['recipe1', 'recipe2', 'recipe3'])
            ->andReturnSelf();

        Recipe::shouldReceive('with')
            ->once()
            ->with(['source', 'classification', 'meals', 'courses', 'preparations'])
            ->andReturnSelf();

        Recipe::shouldReceive('get')
            ->once()
            ->andReturn($recipes);

        $mockPdf = Mockery::mock(DomPDF::class);

        Pdf::shouldReceive('loadView')
            ->once()
            ->with('pdfs.cookbook', Mockery::on(function($data) {
                $cookbook = $data['cookbook'];
                
                // Check that recipes are properly ordered
                if (!isset($cookbook->recipes) || count($cookbook->recipes) !== 3) {
                    return false;
                }
                
                // Check correct order: recipe2 (order 0), recipe1 (order 1), recipe3 (order 2)
                return $cookbook->recipes[0]->_id === 'recipe2' &&
                       $cookbook->recipes[1]->_id === 'recipe1' &&
                       $cookbook->recipes[2]->_id === 'recipe3';
            }))
            ->andReturn($mockPdf);

        $mockPdf->shouldReceive('setPaper')
            ->once()
            ->with('a4', 'portrait')
            ->andReturnSelf();

        // Act
        $result = $this->pdfService->generateCookbookPDF($cookbook);

        // Assert
        $this->assertEquals($mockPdf, $result);
    }

    public function test_generate_cookbook_pdf_handles_empty_recipe_ids()
    {
        // Arrange
        $cookbook = new Cookbook([
            '_id' => 'cookbook123',
            'name' => 'Empty Cookbook',
            'recipe_ids' => null
        ]);

        $mockPdf = Mockery::mock(DomPDF::class);

        // Mock Recipe::whereIn with empty array
        Recipe::shouldReceive('whereIn')
            ->once()
            ->with('_id', [])
            ->andReturnSelf();

        Recipe::shouldReceive('with')
            ->once()
            ->with(['source', 'classification', 'meals', 'courses', 'preparations'])
            ->andReturnSelf();

        Recipe::shouldReceive('get')
            ->once()
            ->andReturn(collect([]));

        Pdf::shouldReceive('loadView')
            ->once()
            ->with('pdfs.cookbook', Mockery::on(function($data) {
                $cookbook = $data['cookbook'];
                return isset($cookbook->recipes) && count($cookbook->recipes) === 0;
            }))
            ->andReturn($mockPdf);

        $mockPdf->shouldReceive('setPaper')
            ->once()
            ->with('a4', 'portrait')
            ->andReturnSelf();

        // Act
        $result = $this->pdfService->generateCookbookPDF($cookbook);

        // Assert
        $this->assertEquals($mockPdf, $result);
    }

    public function test_generate_cookbook_pdf_handles_missing_recipes()
    {
        // Arrange
        $cookbook = new Cookbook([
            '_id' => 'cookbook123',
            'name' => 'Cookbook with Missing Recipes',
            'recipe_ids' => [
                ['recipe_id' => 'recipe1', 'order' => 0],
                ['recipe_id' => 'missing_recipe', 'order' => 1],
                ['recipe_id' => 'recipe3', 'order' => 2]
            ]
        ]);

        // Only return recipes that exist
        $recipes = collect([
            new Recipe(['_id' => 'recipe1', 'name' => 'Recipe 1']),
            new Recipe(['_id' => 'recipe3', 'name' => 'Recipe 3'])
        ]);

        Recipe::shouldReceive('whereIn')
            ->once()
            ->with('_id', ['recipe1', 'missing_recipe', 'recipe3'])
            ->andReturnSelf();

        Recipe::shouldReceive('with')
            ->once()
            ->with(['source', 'classification', 'meals', 'courses', 'preparations'])
            ->andReturnSelf();

        Recipe::shouldReceive('get')
            ->once()
            ->andReturn($recipes);

        $mockPdf = Mockery::mock(DomPDF::class);

        Pdf::shouldReceive('loadView')
            ->once()
            ->with('pdfs.cookbook', Mockery::on(function($data) {
                $cookbook = $data['cookbook'];
                
                // Should only have 2 recipes (missing one should be skipped)
                return isset($cookbook->recipes) && count($cookbook->recipes) === 2;
            }))
            ->andReturn($mockPdf);

        $mockPdf->shouldReceive('setPaper')
            ->once()
            ->with('a4', 'portrait')
            ->andReturnSelf();

        // Act
        $result = $this->pdfService->generateCookbookPDF($cookbook);

        // Assert
        $this->assertEquals($mockPdf, $result);
    }

    public function test_generate_cookbook_pdf_sets_correct_paper_orientation()
    {
        // Arrange
        $cookbook = new Cookbook([
            '_id' => 'cookbook123',
            'name' => 'Test Cookbook',
            'recipe_ids' => []
        ]);

        Recipe::shouldReceive('whereIn->with->get')
            ->andReturn(collect([]));

        $mockPdf = Mockery::mock(DomPDF::class);

        Pdf::shouldReceive('loadView')
            ->andReturn($mockPdf);

        $mockPdf->shouldReceive('setPaper')
            ->once()
            ->with('a4', 'portrait')
            ->andReturnSelf();

        // Act
        $result = $this->pdfService->generateCookbookPDF($cookbook);

        // Assert
        $this->assertEquals($mockPdf, $result);
    }

    public function test_generate_recipe_pdf_sets_correct_paper_orientation()
    {
        // Arrange
        $recipe = Mockery::mock(Recipe::class);
        $mockPdf = Mockery::mock(DomPDF::class);
        
        $recipe->shouldReceive('load')
            ->andReturnSelf();

        Pdf::shouldReceive('loadView')
            ->andReturn($mockPdf);

        $mockPdf->shouldReceive('setPaper')
            ->once()
            ->with('a4', 'portrait')
            ->andReturnSelf();

        // Act
        $result = $this->pdfService->generateRecipePDF($recipe);

        // Assert
        $this->assertEquals($mockPdf, $result);
    }
}
