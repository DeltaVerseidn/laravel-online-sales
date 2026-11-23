<?php 
 
namespace App\Http\Controllers\Api\V1; 
 
use App\Http\Controllers\Controller; 
use App\Http\Requests\Api\CheckoutApiRequest; 
use App\Http\Resources\OrderResource; 
use App\Models\Order; 
use App\Models\Product; 
use App\Models\ShippingService; 
use App\Models\StockMovement; 
use Illuminate\Http\JsonResponse; 
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Str; 
use Illuminate\Validation\ValidationException; 
 
class CheckoutController extends Controller 
{ 
    public function store(CheckoutApiRequest $request): JsonResponse 
    { 
        $validated = $request->validated(); 
        $requestedItems = collect($validated['items'])->keyBy('product_id'); 
 
        $order = DB::transaction(function () use ($request, $validated, $requestedItems) { 
            $shipping = ShippingService::query() 
                ->whereKey($validated['shipping_service_id']) 
                ->where('is_active', true) 
                ->firstOrFail(); 
 
            $products = Product::query() 
                ->whereIn('id', $requestedItems->keys()) 
                ->lockForUpdate() 
                ->get() 
                ->keyBy('id'); 
 
            $subtotal = 0; 
 
            foreach ($requestedItems as $productId => $item) { 
                $product = $products->get($productId); 
                $quantity = (int) $item['quantity']; 
 
                if (! $product || ! $product->is_active || $quantity > $product->stock) { 
                    throw ValidationException::withMessages([ 
                        'items' => ["Stok produk ID {$productId} tidak mencukupi atau produk tidak aktif."], 
                    ]); 
                } 
 
                $subtotal += (float) $product->price * $quantity; 
            } 
 
            $code = 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)); 
 
            $order = Order::create([ 
                'code' => $code, 
                'user_id' => $request->user()->id, 
                'shipping_service_id' => $shipping->id, 
                'recipient_name' => $validated['recipient_name'], 
                'phone' => $validated['phone'], 
                'address' => $validated['address'], 
                'shipping_courier' => $shipping->courier, 
                'shipping_service' => $shipping->service, 
                'shipping_eta' => $shipping->eta, 
                'subtotal' => $subtotal, 
                'shipping_cost' => $shipping->cost, 
                'grand_total' => $subtotal + (float) $shipping->cost, 
  'status' => 'pending', 
                'ordered_at' => now(), 
            ]); 
 
            foreach ($requestedItems as $productId => $item) { 
                $product = $products->get($productId); 
                $quantity = (int) $item['quantity']; 
                $lineTotal = (float) $product->price * $quantity; 
 
                $order->items()->create([ 
                    'product_id' => $product->id, 
                    'product_name' => $product->name, 
                    'price' => $product->price, 
                    'quantity' => $quantity, 
                    'subtotal' => $lineTotal, 
                ]); 
 
                $product->decrement('stock', $quantity); 
 
                StockMovement::create([ 
                    'product_id' => $product->id, 
                    'user_id' => $request->user()->id, 
                    'type' => 'OUT', 
                    'quantity' => $quantity, 
                    'reference' => $code, 
                    'note' => 'Checkout melalui REST API', 
                    'movement_at' => now(), 
                ]); 
            } 
 
            return $order->load(['items', 'user']); 
        }); 
 
        return (new OrderResource($order)) 
            ->additional(['message' => 'Checkout API berhasil. Pesanan menunggu validasi admin.']) 
            ->response() 
            ->setStatusCode(201); 
    } 
} 