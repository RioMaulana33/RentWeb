<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function me()
    {
        return response()->json([
            'user' => auth()->user()
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->post(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ]);
        }
    
        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json([
                'status' => false,
                'message' => 'Email / Password salah!'
            ], 401);
        }
    
        if (!auth()->user()->hasRole('admin')) {
            // Logout jika bukan admin
            auth()->logout();
            
            return response()->json([
                'status' => false,
                'message' => 'Anda tidak memiliki akses untuk login!'
            ], 403);
        }
    
        return response()->json([
            'status' => true,
            'user' => auth()->user(),
            'token' => $token
        ]);
    }

    public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 422);
    }

    try {
        // Create new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 2,
        ]);

        $token = auth()->login($user);

        return response()->json([
            'status' => true,
            'message' => 'Registrasi berhasil',
            'user' => $user,
            'token' => $token
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Registrasi gagal: ' . $e->getMessage()
        ], 500);
    }
}
    public function logout() {
        try {
          auth()->logout(true);
          return response()->json(['status' => true, 'message' => "Berhasil logout."]);
        } catch (JWTException $e) {
          return response()->json(['status' => false, 'message' => 'Sesuatu error terjadi.'], 500);
        }
      }
    
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak ditemukan.'
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password berhasil direset.'
        ]);
    }

    public function secureLogin(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
    
        $checkUser = User::where('email', $request['email'])->first();
    
        if (!$checkUser) {
            return response()->json([
                'status' => false, 
                'message' => 'Email Yang Anda Masukkan Belum Terdaftar!'
            ], 403);
        }
    
        if ($request->remember_me == 1) {
            $token = auth()->setTTL(60 * 24 * 30)->attempt([
                'email' => $request['email'], 
                'password' => $request['password']
            ]);
        } else {
            $token = auth()->attempt([
                'email' => $request['email'], 
                'password' => $request['password']
            ]);
        }
    
        if (!$token) {
            return response()->json([
                'status' => false, 
                'message' => 'Email/Password salah!'
            ], 403);
        }
    
        return response()->json([
            'user' => auth()->user(),
            'token' => $token,
        ], 200);
    }   
  
}
