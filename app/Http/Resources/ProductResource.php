<?php 
 
namespace App\Http\Resources; 
 
use Illuminate\Http\Request; 
use Illuminate\Http\Resources\Json\JsonResource; 
 
class ProductResource extends JsonResource 
{ 
    public function toArray(Request $request): array 
    { 
        return [ 
            'id' => $this->id, 
            'sku' => $this->sku, 
            'name' => $this->name,
              'slug' => $this->slug, 
            'description' => $this->description, 
            'price' => (float) $this->price, 
            'stock' => $this->stock, 
            'is_active' => (bool) $this->is_active, 
            'image' => $this->image, 
            'category' => $this->whenLoaded('category', fn () => [ 
                'id' => $this->category->id, 
                'name' => $this->category->name, 
                'slug' => $this->category->slug, 
            ]), 
            'created_at' => $this->created_at?->toISOString(), 
        ]; 
    } 
} 