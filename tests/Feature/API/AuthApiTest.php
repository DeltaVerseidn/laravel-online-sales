<?php 
 
namespace Tests\Feature\Api; 
 
use App\Models\User; 
use Illuminate\Foundation\Testing\RefreshDatabase; 
use Tests\TestCase; 
 
class AuthApiTest extends TestCase 
{ 
    use RefreshDatabase; 
 
    public function test_customer_can_request_sanctum_token(): void 
    { 
        User::create([ 
            'name' => 'Pelanggan API', 
            'email' => 'customer@example.com', 
            'password' => 'password123', 
            'role' => 'customer', 
        ]); 
 
        $response = $this->postJson('/api/v1/auth/login', [ 
            'email' => 'customer@example.com', 
            'password' => 'password123', 
            'device_name' => 'Postman', 
        ]); 
 
        $response->assertOk() 
            ->assertJsonPath('token_type', 'Bearer') 
            ->assertJsonStructure(['access_token', 'user' => ['id', 'name', 'email', 'role']]); 
    } 
} 