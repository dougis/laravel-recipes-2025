    public function user_can_reset_password_with_valid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('old_password'),
        ]);

        // Create a password reset token
        $token = app('auth.password.broker')->createToken($user);

        $response = $this->postJson('/api/v1/auth/password/reset', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'new_password123',
            'password_confirmation' => 'new_password123',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Your password has been reset',
                 ]);

        // Verify user can login with new password
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'new_password123',
        ]);

        $loginResponse->assertStatus(200);
    }

    /** @test */
    public function password_reset_requires_valid_data()
    {
        $response = $this->postJson('/api/v1/auth/password/reset', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['token', 'email', 'password']);

        $response = $this->postJson('/api/v1/auth/password/reset', [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'new_password',
            'password_confirmation' => 'different_password',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function password_reset_fails_with_invalid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson('/api/v1/auth/password/reset', [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'new_password123',
            'password_confirmation' => 'new_password123',
        ]);

        $response->assertStatus(422);
    }
}
