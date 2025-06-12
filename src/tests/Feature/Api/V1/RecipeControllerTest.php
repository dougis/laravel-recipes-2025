    public function user_cannot_delete_others_recipe()
    {
        $otherUserRecipe = Recipe::factory()->create([
            'user_id' => $this->tier2User->_id,
        ]);

        $response = $this->actingAs($this->user)
                         ->deleteJson("/api/v1/recipes/{$otherUserRecipe->_id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function anyone_can_view_public_recipes()
    {
        // Create public recipes
        Recipe::factory()->count(3)->create([
            'is_private' => false,
        ]);

        // Create private recipe (should not appear)
        Recipe::factory()->create([
            'is_private' => true,
        ]);

        $response = $this->getJson('/api/v1/recipes/public');

        $response->assertStatus(200);
        $this->assertEquals(3, count($response->json('data.recipes')));
    }

    /** @test */
    public function user_can_search_recipes()
    {
        Recipe::factory()->create([
            'name' => 'Chocolate Cake',
            'ingredients' => 'chocolate, flour, eggs',
            'is_private' => false,
        ]);

        Recipe::factory()->create([
            'name' => 'Vanilla Ice Cream',
            'ingredients' => 'vanilla, cream, sugar',
            'is_private' => false,
        ]);

        $response = $this->getJson('/api/v1/recipes/search?query=chocolate');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.recipes')));
        $this->assertEquals('Chocolate Cake', $response->json('data.recipes.0.name'));
    }

    /** @test */
    public function tier2_user_can_toggle_recipe_privacy()
    {
        $recipe = Recipe::factory()->create([
            'user_id' => $this->tier2User->_id,
            'is_private' => false,
        ]);

        $response = $this->actingAs($this->tier2User)
                         ->putJson("/api/v1/recipes/{$recipe->_id}/privacy", [
                             'is_private' => true,
                         ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'data' => [
                         'recipe' => [
                             'is_private' => true,
                         ]
                     ]
                 ]);

        $this->assertDatabaseHas('recipes', [
            '_id' => $recipe->_id,
            'is_private' => true,
        ]);
    }

    /** @test */
    public function non_tier2_user_cannot_toggle_recipe_privacy()
    {
        $recipe = Recipe::factory()->create([
            'user_id' => $this->user->_id,
            'is_private' => false,
        ]);

        $response = $this->actingAs($this->user)
                         ->putJson("/api/v1/recipes/{$recipe->_id}/privacy", [
                             'is_private' => true,
                         ]);

        $response->assertStatus(403);
    }
}
