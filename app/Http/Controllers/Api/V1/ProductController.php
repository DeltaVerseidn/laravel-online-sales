<?php 
 
namespace App\Http\Controllers\Api\V1; 
 
use App\Http\Controllers\Controller; 
use App\Http\Resources\ProductResource; 
use App\Models\Category; 
use App\Models\Product; 
use App\Models\ShippingService; 
use Illuminate\Http\JsonResponse; 
use Illuminate\Http\Request; 
use Illuminate\Http\Resources\Json\AnonymousResourceCollection; 
 
class ProductController extends Controller 
{ 
    public function index(Request $request): AnonymousResourceCollection 
    { 
        $products = Product::query() 
            ->with('category') 
            ->active() 
            ->when($request->filled('q'), function ($query) use ($request) { 
                $keyword = $request->string('q')->toString(); 
                $query->where(function ($sub) use ($keyword) { 
                    $sub->where('name', 'like', "%{$keyword}%") 
                        ->orWhere('sku', 'like', "%{$keyword}%"); 
                }); 
            }) 
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request
>integer('category'))) 
            ->when($request->filled('min_price'), fn ($q) => $q->where('price', '>=', $request
>input('min_price'))) 
            ->when($request->filled('max_price'), fn ($q) => $q->where('price', '<=', $request
>input('max_price'))) 
            ->when($request->boolean('ready'), fn ($q) => $q->where('stock', '>', 0)); 
 
        match ($request->input('sort')) { 
            'price_asc' => $products->orderBy('price'), 
            'price_desc' => $products->orderByDesc('price'), 
            'name' => $products->orderBy('name'), 
            default => $products->latest(), 
        }; 
 
        return ProductResource::collection( 
            $products->paginate($request->integer('per_page', 12))->withQueryString() 
  ); 
    } 
 
    public function show(Product $product): ProductResource 
    { 
        abort_unless($product->is_active, 404); 
        return new ProductResource($product->load('category')); 
    } 
 
    public function categories(): JsonResponse 
    { 
        return response()->json([ 
            'data' => Category::query()->orderBy('name')->get(['id', 'name', 'slug']), 
        ]); 
    } 
 
    public function shippingServices(): JsonResponse 
    { 
        return response()->json([ 
            'data' => ShippingService::query() 
                ->where('is_active', true) 
                ->orderBy('courier') 
                ->orderBy('service') 
                ->get(['id', 'courier', 'service', 'eta', 'cost']), 
        ]); 
    } 
}