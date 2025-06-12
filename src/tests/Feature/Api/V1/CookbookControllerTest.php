    public function user_can_update_own_cookbook()
    {
        $cookbook = Cookbook::factory()->create([
            'user_id' => $this->user->_id,
            'name' => 'Original Cookbook',
        ]);

        $updateData = [
            'name' => 'Updated Cookbook',
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($this->user)
                         ->putJson("/api/v1/cookbooks/{$cookbook->_id}", $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'data' => [
                         'cookbook' => [
                             'name' => 'Updated Cookbook',
                             'description' => 'Updated description',
                         ]
                     ]
                 ]);

        $this->assertDatabaseHas('cookbooks', [
            '_id' => $cookbook->_id,
            'name' => 'Updated Cookbook',
        ]);
    }

    /** @test */
    public function user_can_delete_own_cookbook()
    {
        $cookbook = Cookbook::factory()->create([
            'user_id' => $this->user->_id,
        ]);

        $response = $this->actingAs($this->user)
                         ->deleteJson("/api/v1/cookbooks/{$cookbook->_id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Cookbook deleted successfully',
                 ]);

        $this->assertDatabaseMissing('cookbooks', [
            '_id' => $cookbook->_id,
        ]);
    }

    /** @test */
    public function user_can_add_recipes_to_cookbook()
    {
        $cookbook = Cookbook::factory()->create([
            'user_id' => $this->user->_id,
        ]);

        $recipe1 = Recipe::factory()->create(['user_id' => $this->user->_id]);
        $recipe2 = Recipe::factory()->create(['user_id' => $this->user->_id]);

        $response = $this->actingAs($this->user)
                         ->postJson("/api/v1/cookbooks/{$cookbook->_id}/recipes", [
                             'recipe_ids' => [$recipe1->_id, $recipe2->_id],
                         ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Recipes added to cookbook successfully',
                 ]);

        $cookbook->refresh();
        $this->assertCount(2, $cookbook->recipe_ids);
    }

    /** @test */
    public function user_can_remove_recipe_from_cookbook()
    {
        $recipe = Recipe::factory()->create(['user_id' => $this->user->_id]);
        $cookbook = Cookbook::factory()->create([
            'user_id' => $this->user->_id,
            'recipe_ids' => [
                ['recipe_id' => $recipe->_id, 'order' => 1]
            ],
        ]);

        $response = $this->actingAs($this->user)
                         ->deleteJson("/api/v1/cookbooks/{$cookbook->_id}/recipes/{$recipe->_id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Recipe removed from cookbook successfully',
                 ]);

        $cookbook->refresh();
        $this->assertEmpty($cookbook->recipe_ids);
    }

    /** @test */
    public function anyone_can_view_public_cookbooks()
    {
        // Create public cookbooks
        Cookbook::factory()->count(3)->create([
            'is_private' => false,
        ]);

        // Create private cookbook (should not appear)
        Cookbook::factory()->create([
            'is_private' => true,
        ]);

        $response = $this->getJson('/api/v1/cookbooks/public');

        $response->assertStatus(200);
        $this->assertEquals(3, count($response->json('data.cookbooks')));
    }
}
