<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    // ===============================
    // REGISTRATION TESTS
    // ===============================

    /**
     * Test user can register with valid data.
     */
    public function test_user_can_register_with_valid_data()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        
        $response = $this->postApi('/auth/register', $userData);
        
        $this->assertSuccessResponse($response);
        $response->assertJsonStructure([
            'data' => ['user', 'token']
        ]);
        
        // Verify user was created with correct defaults
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'subscription_tier' => 0 // Should default to free tier
        ]);
        
        // Verify user object in response
        $response->assertJsonPath('data.user.email', 'test@example.com');
        $response->assertJsonPath('data.user.subscription_tier', 0);
        $this->assertNotEmpty($response->json('data.token'));
    }

    /**
     * Test user cannot register with invalid email.
     */
    public function test_user_cannot_register_with_invalid_email()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        
        $response = $this->postApi('/auth/register', $userData);
        
        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user cannot register with duplicate email.
     */
    public function test_user_cannot_register_with_duplicate_email()
    {
        // Create existing user
        $this->createUser(['email' => 'existing@example.com']);
        
        $userData = [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        
        $response = $this->postApi('/auth/register', $userData);
        
        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user cannot register with weak password.
     */
    public function test_user_cannot_register_with_weak_password()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ];
        
        $response = $this->postApi('/auth/register', $userData);
        
        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['password']);
    }

    /**
     * Test user cannot register with mismatched password confirmation.
     */
    public function test_user_cannot_register_with_mismatched_password_confirmation()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ];
        
        $response = $this->postApi('/auth/register', $userData);
        
        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['password']);
    }

    /**
     * Test registration creates free tier user by default.
     */
    public function test_registration_creates_free_tier_user_by_default()
    {
        $userData = [
            'name' => 'Free User',
            'email' => 'free@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        
        $response = $this->postApi('/auth/register', $userData);
        
        $this->assertSuccessResponse($response);
        
        $user = User::where('email', 'free@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(0, $user->subscription_tier);
        $this->assertFalse($user->admin_override);
    }

    /**
     * Test registration returns user and token.
     */
    public function test_registration_returns_user_and_token()
    {
        $userData = [
            'name' => 'Token User',
            'email' => 'token@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        
        $response = $this->postApi('/auth/register', $userData);
        
        $this->assertSuccessResponse($response);
        
        $data = $response->json('data');
        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
        
        // Token should be a string
        $this->assertIsString($data['token']);
    }

    // ===============================
    // LOGIN TESTS
    // ===============================

    /**
     * Test user can login with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials()
    {
        $user = $this->createUser([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
        
        $response = $this->postApi('/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        
        $this->assertSuccessResponse($response);
        $response->assertJsonStructure([
            'data' => ['user', 'token']
        ]);
        
        // Verify user data in response
        $response->assertJsonPath('data.user.email', 'test@example.com');
        $this->assertNotEmpty($response->json('data.token'));
    }

    /**
     * Test user cannot login with invalid email.
     */
    public function test_user_cannot_login_with_invalid_email()
    {
        $response = $this->postApi('/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ]);
        
        $this->assertErrorResponse($response, 401);
        $response->assertJsonPath('message', 'Invalid credentials');
    }

    /**
     * Test user cannot login with wrong password.
     */
    public function test_user_cannot_login_with_wrong_password()
    {
        $user = $this->createUser([
            'email' => 'test@example.com',
            'password' => Hash::make('correct_password')
        ]);
        
        $response = $this->postApi('/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong_password'
        ]);
        
        $this->assertErrorResponse($response, 401);
        $response->assertJsonPath('message', 'Invalid credentials');
    }

    /**
     * Test login returns user and token.
     */
    public function test_login_returns_user_and_token()
    {
        $user = $this->createTier1User([
            'email' => 'tier1@example.com',
            'password' => Hash::make('password123')
        ]);
        
        $response = $this->postApi('/auth/login', [
            'email' => 'tier1@example.com',
            'password' => 'password123'
        ]);
        
        $this->assertSuccessResponse($response);
        
        $data = $response->json('data');
        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('token', $data);
        
        // Verify subscription tier is included
        $this->assertEquals(1, $data['user']['subscription_tier']);
        $this->assertIsString($data['token']);
    }

    /**
     * Test login token can be used for API requests.
     */
    public function test_login_token_can_be_used_for_api_requests()
    {
        $user = $this->createUser([
            'email' => 'api@example.com',
            'password' => Hash::make('password123')
        ]);
        
        // Login to get token
        $loginResponse = $this->postApi('/auth/login', [
            'email' => 'api@example.com',
            'password' => 'password123'
        ]);
        
        $token = $loginResponse->json('data.token');
        
        // Use token to access protected endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getApi('/auth/user');
        
        $this->assertSuccessResponse($response);
        $response->assertJsonPath('data.email', 'api@example.com');
    }

    // ===============================
    // LOGOUT TESTS
    // ===============================

    /**
     * Test user can logout with valid token.
     */
    public function test_user_can_logout_with_valid_token()
    {
        $user = $this->actingAsUser();
        
        $response = $this->postApi('/auth/logout');
        
        $this->assertSuccessResponse($response);
        $response->assertJsonPath('message', 'Successfully logged out');
    }

    /**
     * Test user cannot logout without token.
     */
    public function test_user_cannot_logout_without_token()
    {
        $response = $this->postApi('/auth/logout');
        
        $this->assertUnauthorizedResponse($response);
    }

    /**
     * Test logout invalidates token.
     */
    public function test_logout_invalidates_token()
    {
        $user = $this->createUser();
        
        // Login to get token
        $loginResponse = $this->postApi('/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);
        
        $token = $loginResponse->json('data.token');
        
        // Logout
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postApi('/auth/logout');
        
        $this->assertSuccessResponse($logoutResponse);
        
        // Try to use token after logout
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getApi('/auth/user');
        
        $this->assertUnauthorizedResponse($response);
    }

    // ===============================
    // TOKEN MANAGEMENT TESTS
    // ===============================

    /**
     * Test token provides access to protected endpoints.
     */
    public function test_token_provides_access_to_protected_endpoints()
    {
        $user = $this->actingAsUser();
        
        $response = $this->getApi('/auth/user');
        
        $this->assertSuccessResponse($response);
        $response->assertJsonPath('data.id', $user->_id);
    }

    /**
     * Test invalid token returns unauthorized.
     */
    public function test_invalid_token_returns_unauthorized()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
        ])->getApi('/auth/user');
        
        $this->assertUnauthorizedResponse($response);
    }

    /**
     * Test token includes user subscription info.
     */
    public function test_token_includes_user_subscription_info()
    {
        $user = $this->createTier2User([
            'email' => 'tier2@example.com',
            'password' => Hash::make('password123')
        ]);
        
        $response = $this->postApi('/auth/login', [
            'email' => 'tier2@example.com',
            'password' => 'password123'
        ]);
        
        $this->assertSuccessResponse($response);
        $response->assertJsonPath('data.user.subscription_tier', 2);
        $response->assertJsonPath('data.user.email', 'tier2@example.com');
    }

    /**
     * Test get current authenticated user.
     */
    public function test_get_current_authenticated_user()
    {
        $user = $this->actingAsUser();
        
        $response = $this->getApi('/auth/user');
        
        $this->assertSuccessResponse($response);
        $response->assertJsonStructure([
            'data' => [
                'id', 'name', 'email', 'subscription_tier', 'admin_override'
            ]
        ]);
        $response->assertJsonPath('data.email', $user->email);
    }

    /**
     * Test get current user fails without authentication.
     */
    public function test_get_current_user_fails_without_authentication()
    {
        $response = $this->getApi('/auth/user');
        
        $this->assertUnauthorizedResponse($response);
    }

    // ===============================
    // SECURITY VALIDATION TESTS
    // ===============================

    /**
     * Test registration requires all fields.
     */
    public function test_registration_requires_all_fields()
    {
        $response = $this->postApi('/auth/register', []);
        
        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /**
     * Test login requires email and password.
     */
    public function test_login_requires_email_and_password()
    {
        $response = $this->postApi('/auth/login', []);
        
        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test malformed email validation.
     */
    public function test_malformed_email_validation()
    {
        $testCases = [
            'not-an-email',
            '@example.com',
            'user@',
            'user..user@example.com',
            'user@.com'
        ];
        
        foreach ($testCases as $email) {
            $response = $this->postApi('/auth/register', [
                'name' => 'Test User',
                'email' => $email,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);
            
            $this->assertErrorResponse($response, 422);
            $response->assertJsonValidationErrors(['email']);
        }
    }

    /**
     * Test password strength requirements.
     */
    public function test_password_strength_requirements()
    {
        $weakPasswords = ['', '1', '12', '123', '1234567']; // Less than 8 characters
        
        foreach ($weakPasswords as $password) {
            $response = $this->postApi('/auth/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => $password,
                'password_confirmation' => $password,
            ]);
            
            $this->assertErrorResponse($response, 422);
            $response->assertJsonValidationErrors(['password']);
        }
    }

    // ===============================
    // PASSWORD RESET TESTS
    // ===============================

    /**
     * Test user can request password reset email with valid email.
     */
    public function test_user_can_request_password_reset_email_with_valid_email()
    {
        $user = $this->createUser(['email' => 'reset@example.com']);
        
        $response = $this->postApi('/auth/password/email', [
            'email' => 'reset@example.com'
        ]);
        
        $this->assertSuccessResponse($response);
        $response->assertJsonPath('message', 'We have emailed your password reset link.');
    }

    /**
     * Test password reset email request with invalid email.
     */
    public function test_password_reset_email_request_with_invalid_email()
    {
        $response = $this->postApi('/auth/password/email', [
            'email' => 'nonexistent@example.com'
        ]);
        
        $this->assertErrorResponse($response, 400);
        $response->assertJsonPath('message', "We can't find a user with that email address.");
    }

    /**
     * Test password reset email request requires email field.
     */
    public function test_password_reset_email_request_requires_email_field()
    {
        $response = $this->postApi('/auth/password/email', []);
        
        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test password reset email request with malformed email.
     */
    public function test_password_reset_email_request_with_malformed_email()
    {
        $response = $this->postApi('/auth/password/email', [
            'email' => 'invalid-email'
        ]);
        
        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Test user can reset password with valid token.
     */
    public function test_user_can_reset_password_with_valid_token()
    {
        $user = $this->createUser(['email' => 'reset@example.com']);
        
        // Generate password reset token
        $token = Password::createToken($user);
        
        $response = $this->postApi('/auth/password/reset', [
            'email' => 'reset@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token' => $token
        ]);
        
        $this->assertSuccessResponse($response);
        $response->assertJsonPath('message', 'Your password has been reset.');
        
        // Verify user can login with new password
        $loginResponse = $this->postApi('/auth/login', [
            'email' => 'reset@example.com',
            'password' => 'newpassword123'
        ]);
        
        $this->assertSuccessResponse($loginResponse);
    }

    /**
     * Test password reset with invalid token.
     */
    public function test_password_reset_with_invalid_token()
    {
        $user = $this->createUser(['email' => 'reset@example.com']);
        
        $response = $this->postApi('/auth/password/reset', [
            'email' => 'reset@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token' => 'invalid-token'
        ]);
        
        $this->assertErrorResponse($response, 400);
        $response->assertJsonPath('message', 'This password reset token is invalid.');
    }

    /**
     * Test password reset with mismatched confirmation.
     */
    public function test_password_reset_with_mismatched_confirmation()
    {
        $user = $this->createUser(['email' => 'reset@example.com']);
        $token = Password::createToken($user);
        
        $response = $this->postApi('/auth/password/reset', [
            'email' => 'reset@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'different123',
            'token' => $token
        ]);
        
        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['password']);
    }

    /**
     * Test password reset requires all fields.
     */
    public function test_password_reset_requires_all_fields()
    {
        $response = $this->postApi('/auth/password/reset', []);
        
        $this->assertErrorResponse($response, 422);
        $response->assertJsonValidationErrors(['email', 'password', 'token']);
    }

    /**
     * Test password reset with nonexistent email.
     */
    public function test_password_reset_with_nonexistent_email()
    {
        $response = $this->postApi('/auth/password/reset', [
            'email' => 'nonexistent@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'token' => 'any-token'
        ]);
        
        $this->assertErrorResponse($response, 400);
        $response->assertJsonPath('message', "We can't find a user with that email address.");
    }

    // ===============================
    // EMAIL VERIFICATION TESTS
    // ===============================

    /**
     * Test user can verify email with valid parameters.
     */
    public function test_user_can_verify_email_with_valid_parameters()
    {
        $user = $this->createUser([
            'email' => 'verify@example.com',
            'email_verified_at' => null
        ]);
        
        $hash = sha1($user->getEmailForVerification());
        
        $response = $this->getApi("/auth/email/verify/{$user->_id}/{$hash}");
        
        $this->assertSuccessResponse($response);
        $response->assertJsonPath('message', 'Email verified successfully');
        
        // Verify user is now verified
        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }

    /**
     * Test email verification with invalid user ID.
     */
    public function test_email_verification_with_invalid_user_id()
    {
        $response = $this->getApi('/auth/email/verify/invalid-id/some-hash');
        
        $this->assertNotFoundResponse($response);
    }

    /**
     * Test email verification with wrong hash.
     */
    public function test_email_verification_with_wrong_hash()
    {
        $user = $this->createUser([
            'email' => 'verify@example.com',
            'email_verified_at' => null
        ]);
        
        $response = $this->getApi("/auth/email/verify/{$user->_id}/wrong-hash");
        
        $this->assertForbiddenResponse($response);
        $response->assertJsonPath('message', 'Invalid hash');
    }

    /**
     * Test email verification when already verified.
     */
    public function test_email_verification_when_already_verified()
    {
        $user = $this->createUser([
            'email' => 'verified@example.com',
            'email_verified_at' => now()
        ]);
        
        $hash = sha1($user->getEmailForVerification());
        
        $response = $this->getApi("/auth/email/verify/{$user->_id}/{$hash}");
        
        $this->assertSuccessResponse($response);
        $response->assertJsonPath('message', 'Email already verified');
    }

    /**
     * Test email verification with mismatched user ID.
     */
    public function test_email_verification_with_mismatched_user_id()
    {
        $user1 = $this->createUser(['email' => 'user1@example.com']);
        $user2 = $this->createUser(['email' => 'user2@example.com']);
        
        $hash = sha1($user1->getEmailForVerification());
        
        $response = $this->getApi("/auth/email/verify/{$user2->_id}/{$hash}");
        
        $this->assertForbiddenResponse($response);
        $response->assertJsonPath('message', 'Invalid user ID');
    }

    // ===============================
    // USER PROFILE OPERATION TESTS
    // ===============================

    /**
     * Test authenticated user can retrieve their profile.
     */
    public function test_authenticated_user_can_retrieve_profile()
    {
        $user = $this->actingAsUser();
        
        $response = $this->getApi('/auth/user');
        
        $this->assertSuccessResponse($response);
        $response->assertJsonStructure([
            'data' => [
                'id', 'name', 'email', 'subscription_tier', 
                'admin_override', 'email_verified_at'
            ]
        ]);
        $response->assertJsonPath('data.email', $user->email);
        $response->assertJsonPath('data.subscription_tier', $user->subscription_tier);
    }

    /**
     * Test unauthenticated user cannot retrieve profile.
     */
    public function test_unauthenticated_user_cannot_retrieve_profile()
    {
        $response = $this->getApi('/auth/user');
        
        $this->assertUnauthorizedResponse($response);
    }

    /**
     * Test user profile includes subscription information.
     */
    public function test_user_profile_includes_subscription_information()
    {
        $user = $this->createTier2User();
        $this->actingAs($user, 'sanctum');
        
        $response = $this->getApi('/auth/user');
        
        $this->assertSuccessResponse($response);
        $response->assertJsonPath('data.subscription_tier', 2);
        $response->assertJsonPath('data.admin_override', false);
    }

    /**
     * Test admin user profile shows admin status.
     */
    public function test_admin_user_profile_shows_admin_status()
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'sanctum');
        
        $response = $this->getApi('/auth/user');
        
        $this->assertSuccessResponse($response);
        $response->assertJsonPath('data.subscription_tier', 100);
        $response->assertJsonPath('data.admin_override', true);
    }

    // ===============================
    // RATE LIMITING TESTS
    // ===============================

    /**
     * Test login rate limiting after multiple failed attempts.
     */
    public function test_login_rate_limiting_after_multiple_failed_attempts()
    {
        $user = $this->createUser([
            'email' => 'ratelimit@example.com',
            'password' => Hash::make('correct_password')
        ]);
        
        // Make 5 failed attempts (assuming default Laravel rate limit)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postApi('/auth/login', [
                'email' => 'ratelimit@example.com',
                'password' => 'wrong_password'
            ]);
            
            $this->assertErrorResponse($response, 401);
        }
        
        // 6th attempt should be rate limited
        $response = $this->postApi('/auth/login', [
            'email' => 'ratelimit@example.com',
            'password' => 'wrong_password'
        ]);
        
        $this->assertErrorResponse($response, 429); // Too Many Requests
    }

    /**
     * Test password reset email rate limiting.
     */
    public function test_password_reset_email_rate_limiting()
    {
        $user = $this->createUser(['email' => 'resetlimit@example.com']);
        
        // Make multiple requests in rapid succession
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postApi('/auth/password/email', [
                'email' => 'resetlimit@example.com'
            ]);
            
            if ($i < 5) {
                // First 5 should succeed
                $this->assertSuccessResponse($response);
            } else {
                // 6th should be rate limited
                $this->assertErrorResponse($response, 429);
            }
        }
    }

    /**
     * Test registration rate limiting per IP.
     */
    public function test_registration_rate_limiting_per_ip()
    {
        // Make multiple registration attempts
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postApi('/auth/register', [
                'name' => "User {$i}",
                'email' => "user{$i}@example.com",
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);
            
            if ($i < 5) {
                // First 5 should succeed
                $this->assertSuccessResponse($response);
            } else {
                // 6th should be rate limited
                $this->assertErrorResponse($response, 429);
            }
        }
    }
}