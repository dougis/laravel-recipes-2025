    public function password_update_requires_correct_current_password()
    {
        $this->user->update(['password' => Hash::make('old_password')]);

        $response = $this->actingAs($this->user)
                         ->putJson('/api/v1/users/profile', [
                             'current_password' => 'wrong_password',
                             'password' => 'new_password123',
                             'password_confirmation' => 'new_password123',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['current_password']);
    }

    /** @test */
    public function user_can_get_subscription_details()
    {
        $response = $this->actingAs($this->user)
                         ->getJson('/api/v1/users/subscription');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'subscription' => [
                             'current_tier',
                             'tier_name',
                             'features',
                             'limits',
                         ],
                         'available_tiers' => [
                             '*' => [
                                 'id',
                                 'name',
                                 'tier',
                                 'price',
                                 'features',
                             ]
                         ]
                     ]
                 ])
                 ->assertJson([
                     'status' => 'success',
                     'data' => [
                         'subscription' => [
                             'current_tier' => 1,
                         ]
                     ]
                 ]);
    }

    /** @test */
    public function user_can_update_subscription()
    {
        $response = $this->actingAs($this->user)
                         ->postJson('/api/v1/users/subscription', [
                             'tier' => 2,
                             'payment_method_id' => 'pm_test_card',
                         ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Subscription updated successfully',
                 ]);

        $this->user->refresh();
        $this->assertEquals(2, $this->user->subscription_tier);
    }

    /** @test */
    public function subscription_update_requires_valid_tier()
    {
        $response = $this->actingAs($this->user)
                         ->postJson('/api/v1/users/subscription', [
                             'tier' => 999, // Invalid tier
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['tier']);
    }

    /** @test */
    public function subscription_downgrade_to_free_works()
    {
        $response = $this->actingAs($this->user)
                         ->postJson('/api/v1/users/subscription', [
                             'tier' => 0, // Downgrade to free
                         ]);

        $response->assertStatus(200);

        $this->user->refresh();
        $this->assertEquals(0, $this->user->subscription_tier);
    }

    /** @test */
    public function user_gets_correct_feature_limits_based_on_tier()
    {
        // Test free user limits
        $freeUser = User::factory()->create(['subscription_tier' => 0]);
        
        $response = $this->actingAs($freeUser)
                         ->getJson('/api/v1/users/subscription');

        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'subscription' => [
                             'limits' => [
                                 'max_recipes' => 25,
                                 'max_cookbooks' => 1,
                                 'can_create_private' => false,
                             ]
                         ]
                     ]
                 ]);

        // Test tier 2 user limits
        $tier2User = User::factory()->create(['subscription_tier' => 2]);
        
        $response = $this->actingAs($tier2User)
                         ->getJson('/api/v1/users/subscription');

        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'subscription' => [
                             'limits' => [
                                 'max_recipes' => -1, // Unlimited
                                 'max_cookbooks' => -1, // Unlimited
                                 'can_create_private' => true,
                             ]
                         ]
                     ]
                 ]);
    }
}
