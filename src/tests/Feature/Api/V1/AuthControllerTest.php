<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

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
}