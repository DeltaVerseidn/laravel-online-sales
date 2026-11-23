<?php 
 
namespace App\Http\Requests\Api; 
 
use Illuminate\Foundation\Http\FormRequest; 
 
class CheckoutApiRequest extends FormRequest 
{ 
    public function authorize(): bool 
    { 
        return $this->user()?->role === 'customer'; 
    } 
 
    public function rules(): array 
    { 
        return [ 
            'recipient_name' => ['required', 'string', 'max:100'], 
            'phone' => ['required', 'string', 'max:30'], 
            'address' => ['required', 'string', 'max:1000'], 
            'shipping_service_id' => ['required', 'integer', 'exists:shipping_services,id'], 
            'items' => ['required', 'array', 'min:1'], 
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'], 
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'], 
 ]; 
    } 
}