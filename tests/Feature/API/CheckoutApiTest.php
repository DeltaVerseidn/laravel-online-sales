<?php 
 
namespace Tests\Feature\Api; 
 
use App\Models\Category; 
use App\Models\Product; 
use App\Models\ShippingService; 
use App\Models\User; 
use Illuminate\Foundation\Testing\RefreshDatabase; 
use Laravel\Sanctum\Sanctum; 
use Tests\TestCase; 
 
class CheckoutApiTest extends TestCase 
{ 
    use RefreshDatabase; 
 
    public function test_customer_can_checkout_and_stock_is_decreased(): void 
    { 
        $customer = User::create([ 
            'name' => 'Pelanggan', 
            'email' => 'customer@example.com', 
            'password' => 'password123', 
            'role' => 'customer', 
        ]); 
        Sanctum::actingAs($customer, ['customer']); 
 
        $category = Category::create(['name' => 'Elektronik', 'slug' => 'elektronik']); 
        $product = Product::create([ 
            'category_id' => $category->id, 
            'sku' => 'SKU-002', 
            'name' => 'Mouse Wireless', 
            'slug' => 'mouse-wireless', 
            'description' => 'Produk contoh', 
            'price' => 150000, 
            'stock' => 8, 
            'is_active' => true, 
        ]); 
        $shipping = ShippingService::create([ 
            'courier' => 'JNE', 
            'service' => 'REG', 
            'eta' => '2-3 hari', 
            'cost' => 20000, 
            'is_active' => true, 
        ]); 
 
        $response = $this->postJson('/api/v1/checkout', [ 
            'recipient_name' => 'Pelanggan', 
            'phone' => '081234567890', 
            'address' => 'Purwokerto', 
            'shipping_service_id' => $shipping->id, 
            'items' => [ 
                ['product_id' => $product->id, 'quantity' => 2], 
            ], 
        ]); 
 
        $response->assertCreated()->assertJsonPath('data.status', 'pending'); 
        $this->assertDatabaseHas('orders', ['user_id' => $customer->id, 'status' => 'pending']); 
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'type' => 'OUT', 'quantity' 
=> 2]); 
        $this->assertSame(6, $product->fresh()->stock); 
    } 
} 