<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;

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

        if (!auth()->user()->hasRole(['admin', 'admin-kota'])) {
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

    public function initializeRegistration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // Generate and store OTP
            $otpCode = sprintf("%06d", mt_rand(1, 999999));
            $cacheKey = 'registration_' . $request->email;

            Cache::put($cacheKey, [
                'name' => $request->name,
                'email' => $request->email,
                'verification_code' => $otpCode,
                'token_expired_at' => now()->addMinutes(2)
            ], now()->addMinutes(2));

            // Send OTP email
            $response = Http::withHeaders([
                'api-key' => env('SENDINBLUE_API_KEY'),
                "Content-Type" => "application/json"
            ])->post('https://api.brevo.com/v3/smtp/email', [
                "sender" => [
                    "name" => env('SENDINBLUE_SENDER_NAME'),
                    "email" => env('SENDINBLUE_SENDER_EMAIL'),
                ],
                'to' => [
                    ['email' => $request->email]
                ],
                "subject" => "Verifikasi Email Registrasi",
                "htmlContent" => "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 20px;'>
                    <div style='max-width: 500px; margin: 0 auto;'>
                        <h1 style='color: #2c5282; font-size: 22px; margin-bottom: 20px;'>Verifikasi Email</h1>
                        
                        <p style='margin-bottom: 25px;'>Terima kasih telah mendaftar. Untuk mengaktifkan akun Anda, masukkan kode OTP berikut:</p>
                        
                        <div style='background-color: #f7fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 20px; text-align: center; margin-bottom: 25px;'>
                            <div style='color: #2c5282; font-size: 28px; font-weight: bold; letter-spacing: 4px;'>{$otpCode}</div>
                        </div>
                        
                        <p style='color: #4a5568; font-size: 14px; margin-bottom: 15px;'>Kode OTP ini akan kadaluarsa dalam 2 menit.</p>
                    </div>
                </body>
                </html>"
            ]);

            if (!$response->successful()) {
                throw new \Exception('Gagal mengirim kode OTP verifikasi');
            }

            return response()->json([
                'status' => true,
                'message' => 'Kode OTP telah dikirim ke email Anda.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal memulai proses registrasi: ' . $e->getMessage()
            ], 500);
        }
    }

    // Step 2: Verify OTP
    public function verifyRegistrationOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $cacheKey = 'registration_' . $request->email;
        $tempRegistration = Cache::get($cacheKey);

        if (
            !$tempRegistration ||
            $tempRegistration['verification_code'] !== $request->otp ||
            now()->isAfter($tempRegistration['token_expired_at'])
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Kode OTP tidak valid atau sudah kadaluarsa'
            ], 400);
        }

        Cache::put($cacheKey, [
            'name' => $tempRegistration['name'],
            'email' => $tempRegistration['email'],
            'is_verified' => true
        ], now()->addMinutes(30));

        return response()->json([
            'status' => true,
            'message' => 'Verifikasi OTP berhasil'
        ]);
    }

    // Step 3: Complete registration
    public function completeRegistration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:10',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $cacheKey = 'registration_' . $request->email;
        $tempRegistration = Cache::get($cacheKey);

        if (!$tempRegistration) {
            return response()->json([
                'status' => false,
                'message' => 'Data registrasi tidak ditemukan'
            ], 400);
        }

        try {
            // Create the user
            $user = User::create([
                'name' => $tempRegistration['name'],
                'email' => $tempRegistration['email'],
                'phone' => $request->phone,
                'password' => bcrypt($request->password),
                'email_verified_at' => now(),
            ]);

            // Assign default role
            $user->assignRole('user');

            // Clear temporary registration data
            Cache::forget($cacheKey);

            // Generate JWT token
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

    public function resendRegistrationOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $cacheKey = 'registration_' . $request->email;
        $tempRegistration = Cache::get($cacheKey);


        if (!$tempRegistration) {
            return response()->json([
                'status' => false,
                'message' => 'Data registrasi tidak ditemukan'
            ], 404);
        }

        // Generate new OTP and update session
        $otpCode = sprintf("%06d", mt_rand(1, 999999));

        // Update cache dengan OTP baru
        Cache::put($cacheKey, [
            'name' => $tempRegistration['name'],
            'email' => $tempRegistration['email'],
            'verification_code' => $otpCode,
            'token_expired_at' => now()->addMinutes(2)
        ], now()->addMinutes(2));

        // Send new OTP email
        $response = Http::withHeaders([
            'api-key' => env('SENDINBLUE_API_KEY'),
            "Content-Type" => "application/json"
        ])->post('https://api.brevo.com/v3/smtp/email', [
            "sender" => [
                "name" => env('SENDINBLUE_SENDER_NAME'),
                "email" => env('SENDINBLUE_SENDER_EMAIL'),
            ],
            'to' => [
                ['email' => $request->email]
            ],
            "subject" => "Verifikasi Email Registrasi",
            "htmlContent" => "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 20px;'>
                <div style='max-width: 500px; margin: 0 auto;'>
                    <h1 style='color: #2c5282; font-size: 22px; margin-bottom: 20px;'>Verifikasi Email</h1>
                    
                    <p style='margin-bottom: 25px;'>Berikut adalah kode OTP baru Anda:</p>
                    
                    <div style='background-color: #f7fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 20px; text-align: center; margin-bottom: 25px;'>
                        <div style='color: #2c5282; font-size: 28px; font-weight: bold; letter-spacing: 4px;'>{$otpCode}</div>
                    </div>
                    
                    <p style='color: #4a5568; font-size: 14px; margin-bottom: 15px;'>Kode OTP ini akan kadaluarsa dalam 2 menit.</p>
                </div>
            </body>
            </html>"
        ]);

        if ($response->successful()) {
            return response()->json([
                'status' => true,
                'message' => 'Kode OTP baru telah dikirim'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengirim kode OTP baru'
            ], 500);
        }
    }

    public function logout()
    {
        try {
            auth()->logout(true);
            return response()->json(['status' => true, 'message' => "Berhasil logout."]);
        } catch (JWTException $e) {
            return response()->json(['status' => false, 'message' => 'Sesuatu error terjadi.'], 500);
        }
    }

    // public function resetPassword(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required|email',
    //         'password' => 'required|min:8|confirmed',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $validator->errors()->first()
    //         ], 422);
    //     }

    //     $user = User::where('email', $request->email)->first();

    //     if (!$user) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'User tidak ditemukan.'
    //         ], 404);
    //     }

    //     $user->password = Hash::make($request->password);
    //     $user->save();

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Password berhasil direset.'
    //     ]);
    // }

    public function secureLogin(Request $request)
    {
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

    public function mailAdmin()
    {
        // Email admin yang tetap
        $adminEmail = 'blucarra552@gmail.com';

        // Generate OTP code (6 digits)
        $otpCode = sprintf("%06d", mt_rand(1, 999999));

        $adminUser = User::where('email', $adminEmail)->first();
        if ($adminUser) {
            $adminUser->verification_code = $otpCode;
            $adminUser->token_expired_at = now()->addMinutes(2);
            $adminUser->save();
        }

        $response = Http::withHeaders([
            'api-key' => env('SENDINBLUE_API_KEY'),
            "Content-Type" => "application/json"
        ])->post('https://api.brevo.com/v3/smtp/email', [
            "sender" => [
                "name" => env('SENDINBLUE_SENDER_NAME'),
                "email" => env('SENDINBLUE_SENDER_EMAIL'),
            ],
            'to' => [
                ['email' => $adminEmail]
            ],
            "subject" => "Kode OTP Reset Password Admin",
            "htmlContent" => "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 20px;'>
                <div style='max-width: 500px; margin: 0 auto;'>
                    <h1 style='color: #2c5282; font-size: 22px; margin-bottom: 20px;'>Reset Password</h1>
                    
                    <p style='margin-bottom: 25px;'>Anda telah meminta untuk mereset password akun Anda. Berikut adalah kode OTP Anda:</p>
                    
                    <div style='background-color: #f7fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 20px; text-align: center; margin-bottom: 25px;'>
                        <div style='color: #2c5282; font-size: 28px; font-weight: bold; letter-spacing: 4px;'>{$otpCode}</div>
                    </div>
                    
                    <p style='color: #4a5568; font-size: 14px; margin-bottom: 15px;'>Kode OTP ini akan kadaluarsa dalam 2 menit.</p>
                    
                    <p style='color: #718096; font-size: 13px;'>Jika Anda tidak meminta reset password, abaikan email ini.</p>
                </div>
            </body>
            </html>",
        ]);

        if ($response->successful()) {
            return response()->json([
                'status' => true,
                'message' => 'Kode OTP telah dikirim ke email admin',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengirim kode OTP',
            ], 500);
        }
    }

    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $adminUser = User::where('verification_code', $request->otp)
            ->where('email', 'blucarra552@gmail.com')
            ->where('token_expired_at', '>', now())
            ->first();

        if (!$adminUser) {
            return response()->json([
                'status' => false,
                'message' => 'Kode OTP tidak valid atau sudah kadaluarsa'
            ], 400);
        }

        $adminUser->email_verified_at = now();
        $adminUser->verification_code = null;
        $adminUser->save();

        return response()->json([
            'status' => true,
            'message' => 'Verifikasi OTP berhasil'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $adminUser = User::where('email', 'blucarra552@gmail.com')->first();

        if (!$adminUser) {
            return response()->json([
                'status' => false,
                'message' => 'Admin user tidak ditemukan.'
            ], 404);
        }

        // Check if email is verified
        if (!$adminUser->email_verified_at) {
            return response()->json([
                'status' => false,
                'message' => 'Email admin belum diverifikasi. Silakan verifikasi OTP terlebih dahulu.'
            ], 403);
        }

        $adminUser->password = Hash::make($request->password);
        $adminUser->save();

        return response()->json([
            'status' => true,
            'message' => 'Password admin berhasil direset.'
        ]);
    }

    public function formPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'uuid' => 'required|exists:users,uuid',
        'password' => 'required|min:8|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        $user = User::where('uuid', $request->uuid)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password berhasil diubah'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Gagal mengubah password: ' . $e->getMessage()
        ], 500);
    }
}


    public function sendUserOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
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
                'message' => 'Email tidak terdaftar dalam sistem.'
            ], 404);
        }

        // Generate OTP code (6 digits)
        $otpCode = sprintf("%06d", mt_rand(1, 999999));

        // Save OTP to user record
        $user->verification_code = $otpCode;
        $user->token_expired_at = now()->addMinutes(2);
        $user->save();

        $response = Http::withHeaders([
            'api-key' => env('SENDINBLUE_API_KEY'),
            "Content-Type" => "application/json"
        ])->post('https://api.brevo.com/v3/smtp/email', [
            "sender" => [
                "name" => env('SENDINBLUE_SENDER_NAME'),
                "email" => env('SENDINBLUE_SENDER_EMAIL'),
            ],
            'to' => [
                ['email' => $request->email]
            ],
            "subject" => "Kode OTP Reset Password",
            "htmlContent" => "
             <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 20px;'>
                <div style='max-width: 500px; margin: 0 auto;'>
                    <h1 style='color: #2c5282; font-size: 22px; margin-bottom: 20px;'>Reset Password</h1>
                    
                    <p style='margin-bottom: 25px;'>Anda telah meminta untuk mereset password akun Anda. Berikut adalah kode OTP Anda:</p>
                    
                    <div style='background-color: #f7fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 20px; text-align: center; margin-bottom: 25px;'>
                        <div style='color: #2c5282; font-size: 28px; font-weight: bold; letter-spacing: 4px;'>{$otpCode}</div>
                    </div>
                    
                    <p style='color: #4a5568; font-size: 14px; margin-bottom: 15px;'>Kode OTP ini akan kadaluarsa dalam 2 menit.</p>
                    
                    <p style='color: #718096; font-size: 13px;'>Jika Anda tidak meminta reset password, abaikan email ini.</p>
                </div>
            </body>
            </html>",
        ]);

        if ($response->successful()) {
            return response()->json([
                'status' => true,
                'message' => 'Kode OTP telah dikirim ke email Anda',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengirim kode OTP',
            ], 500);
        }
    }

    public function verifyUserOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = User::where('email', $request->email)
            ->where('verification_code', $request->otp)
            ->where('token_expired_at', '>', now())
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Kode OTP tidak valid atau sudah kadaluarsa'
            ], 400);
        }

        $user->email_verified_at = now();
        $user->verification_code = null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Verifikasi OTP berhasil'
        ]);
    }

    public function resetUserPassword(Request $request)
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

        // Check if email is verified
        if (!$user->email_verified_at) {
            return response()->json([
                'status' => false,
                'message' => 'Email belum diverifikasi. Silakan verifikasi OTP terlebih dahulu.'
            ], 403);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password berhasil direset.'
        ]);
    }
}
