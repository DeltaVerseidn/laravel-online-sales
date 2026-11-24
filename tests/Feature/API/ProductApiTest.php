<?php 
 
namespace Tests\Feature\Api; 
 
use App\Models\Category; 
use App\Models\Product; 
use Illuminate\Foundation\Testing\RefreshDatabase; 
use Tests\TestCase; 
 
class ProductApiTest extends TestCase 
{ 
    use RefreshDatabase; 
 
    public function test_public_can_read_active_products(): void 
    { 
        $category = Category::create(['name' => 'Elektronik', 'slug' => 'elektronik']); 
 
        Product::create([ 
            'category_id' => $category->id, 
            'sku' => 'SKU-001', 
            'name' => 'Keyboard Mechanical', 
            'slug' => 'keyboard-mechanical', 
            'description' => 'Produk contoh', 
            'price' => 350000, 
            'stock' => 10, 
            'is_active' => true, 
        ]); 
 
        $response = $this->getJson('/api/v1/products'); 
 
        $response->assertOk() 
            ->assertJsonFragment(['name' => 'Keyboard Mechanical']); 
    } 
}