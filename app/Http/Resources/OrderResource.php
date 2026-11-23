<?php 
namespace App\Http\Resources; 
 
use Illuminate\Http\Request; 
use Illuminate\Http\Resources\Json\JsonResource; 
 
class OrderResource extends JsonResource 
{ 
    public function toArray(Request $request): array 
    { 
        return [ 
            'id' => $this->id, 
            'code' => $this->code, 
            'status' => $this->status, 
            'recipient_name' => $this->recipient_name, 
            'phone' => $this->phone, 
            'address' => $this->address, 
            'shipping' => [ 
                'courier' => $this->shipping_courier, 
                'service' => $this->shipping_service, 
                'eta' => $this->shipping_eta, 
                'cost' => (float) $this->shipping_cost, 
            ], 
            'subtotal' => (float) $this->subtotal, 
            'grand_total' => (float) $this->grand_total, 
            'ordered_at' => $this->ordered_at?->toISOString(), 
            'validated_at' => $this->validated_at?->toISOString(), 
            'customer' => $this->whenLoaded('user', fn () => [ 
                'id' => $this->user->id, 
                'name' => $this->user->name, 
                'email' => $this->user->email, 
            ]), 
            'items' => OrderItemResource::collection($this->whenLoaded('items')), 
        ]; 
    } 
} 