<?php 
 
namespace App\Http\Controllers\Api\V1; 
 
use App\Http\Controllers\Controller; 
use App\Http\Requests\Api\LoginRequest; 
use App\Models\User; 
use Illuminate\Http\JsonResponse; 
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Hash; 
 
class AuthController extends Controller 
{ 
    public function login(LoginRequest $request): JsonResponse 
    { 
        $user = User::where('email', $request->string('email'))->first(); 
          if (! $user || ! Hash::check($request->string('password'), $user->password)) { 
            return response()->json([ 
                'message' => 'Email atau password tidak valid.', 
            ], 422); 
        } 
 
        $abilities = $user->role === 'admin' ? ['admin'] : ['customer']; 
        $token = $user->createToken( 
            $request->string('device_name')->toString(), 
            $abilities 
        )->plainTextToken; 
 
        return response()->json([ 
            'message' => 'Login API berhasil.', 
            'token_type' => 'Bearer', 
            'access_token' => $token, 
            'user' => [ 
                'id' => $user->id, 
                'name' => $user->name, 
                'email' => $user->email, 
                'role' => $user->role, 
            ], 
        ]); 
    } 
 
    public function me(Request $request): JsonResponse 
    { 
        return response()->json(['data' => $request->user()]); 
    } 
 
    public function logout(Request $request): JsonResponse 
    { 
        $request->user()->currentAccessToken()?->delete(); 
 
        return response()->json([ 
            'message' => 'Token aktif telah dicabut.', 
        ]); 
    } 
} 