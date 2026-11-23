<?php 
 
namespace App\Http\Controllers\Api\V1; 
 
use App\Http\Controllers\Controller; 
use App\Http\Resources\OrderResource; 
use App\Models\Order; 
use Illuminate\Http\Request; 
use Illuminate\Http\Resources\Json\AnonymousResourceCollection; 
 
class CustomerOrderController extends Controller 
{ 
    public function index(Request $request): AnonymousResourceCollection 
    { 
        $orders = Order::query() 
            ->where('user_id', $request->user()->id) 
            ->with('items') 
   ->latest('ordered_at') 
            ->paginate(10); 
 
        return OrderResource::collection($orders); 
    } 
 
    public function show(Request $request, Order $order): OrderResource 
    { 
        abort_unless($order->user_id === $request->user()->id, 404); 
        return new OrderResource($order->load('items')); 
    } 
}