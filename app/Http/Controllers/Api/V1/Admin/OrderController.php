<?php 
 
namespace App\Http\Controllers\Api\V1\Admin; 
 
use App\Http\Controllers\Controller; 
use App\Http\Resources\OrderResource; 
use App\Models\Order; 
use App\Models\Product; 
use App\Models\StockMovement; 
use Illuminate\Http\JsonResponse; 
use Illuminate\Http\Request; 
use Illuminate\Http\Resources\Json\AnonymousResourceCollection; 
use Illuminate\Support\Facades\DB; 
use Illuminate\Validation\Rule; 
 
class OrderController extends Controller 
{ 
    public function index(Request $request): AnonymousResourceCollection 
    { 
        $orders = Order::query() 
            ->with(['user', 'items']) 
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status'))) 
            ->when($request->filled('q'), function ($q) use ($request) { 
                $keyword = $request->string('q')->toString(); 
                $q->where(function ($sub) use ($keyword) { 
                    $sub->where('code', 'like', "%{$keyword}%") 
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$keyword}%")); 
                }); 
            }) 
            ->latest('ordered_at') 
            ->paginate(15); 
 
        return OrderResource::collection($orders); 
    } 
 
    public function show(Order $order): OrderResource 
    { 
        return new OrderResource($order->load(['user', 'items'])); 
    } 
 
    public function confirm(Request $request, Order $order): JsonResponse 
    { 
        if ($order->status !== 'pending') { 
            return response()->json(['message' => 'Hanya pesanan pending yang dapat divalidasi.'], 409); 
 } 
 
        $order->update([ 
            'status' => 'confirmed', 
            'validated_by' => $request->user()->id, 
            'validated_at' => now(), 
        ]); 
 
        return response()->json([ 
            'message' => 'Pesanan berhasil divalidasi.', 
            'data' => new OrderResource($order->fresh()->load(['user', 'items'])), 
        ]); 
    } 
 
    public function updateStatus(Request $request, Order $order): JsonResponse 
    { 
        $data = $request->validate([ 
            'status' => ['required', Rule::in(['processed', 'shipped', 'completed'])], 
        ]); 
 
        $allowed = [ 
            'confirmed' => ['processed'], 
            'processed' => ['shipped'], 
            'shipped' => ['completed'], 
        ]; 
 
        if (! in_array($data['status'], $allowed[$order->status] ?? [], true)) { 
            return response()->json(['message' => 'Perubahan status tidak sesuai alur transaksi.'], 409); 
        } 
 
        $order->update(['status' => $data['status']]); 
 
        return response()->json([ 
            'message' => 'Status pesanan diperbarui.', 
            'data' => new OrderResource($order->fresh()->load(['user', 'items'])), 
        ]); 
    } 
 
    public function cancel(Request $request, Order $order): JsonResponse 
    { 
        if (! in_array($order->status, ['pending', 'confirmed'], true)) { 
            return response()->json(['message' => 'Pesanan pada status ini tidak dapat dibatalkan.'], 409); 
        } 
 
        DB::transaction(function () use ($request, $order) { 
            $order->load('items'); 
 
            foreach ($order->items as $item) { 
                if (! $item->product_id) { 
                    continue; 
                } 
 
                $product = Product::whereKey($item->product_id)->lockForUpdate()->first(); 
                if (! $product) { 
                    continue; 
                } 
 
                $product->increment('stock', $item->quantity); 
 
                StockMovement::create([ 
                    'product_id' => $product->id, 
                    'user_id' => $request->user()->id, 
                    'type' => 'IN', 
                    'quantity' => $item->quantity, 
                    'reference' => 'CANCEL-'.$order->code, 
                    'note' => 'Pengembalian stok karena pesanan dibatalkan melalui REST API', 
                    'movement_at' => now(), 
                ]); 
            } 
 
            $order->update([ 
                'status' => 'cancelled', 
                'validated_by' => $request->user()->id,
                 'validated_at' => now(), 
            ]); 
        }); 
 
        return response()->json([ 
            'message' => 'Pesanan dibatalkan dan stok dikembalikan.', 
            'data' => new OrderResource($order->fresh()->load(['user', 'items'])), 
        ]); 
    } 
} 