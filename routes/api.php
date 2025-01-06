    <?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\KotaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MobilController;
use App\Http\Controllers\PenyewaanController;
use App\Http\Controllers\StokMobilController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Route
Route::middleware(['auth', 'json'])->prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->withoutMiddleware('auth');
    Route::post('register', [AuthController::class, 'register'])->withoutMiddleware('auth');
    Route::post('secure/login', [AuthController::class, 'secureLogin'])->withoutMiddleware('auth');
    Route::delete('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::post('change-password', [UserController::class, 'changePassword'])->withoutMiddleware('auth');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->withoutMiddleware('auth');

    Route::prefix('user')->group(function () {
        Route::post('update', [UserController::class, 'updateMobile'])->withoutMiddleware('auth');
    });
});

Route::prefix('setting')->group(function () {   
    Route::get('', [SettingController::class, 'index']);
});

Route::middleware(['auth', 'verified', 'json'])->group(function () {
    Route::prefix('setting')->middleware('can:setting')->group(function () {
        Route::post('', [SettingController::class, 'update']);
    });

    Route::prefix('master')->group(function () {
        Route::middleware('can:master-user')->group(function () {
            Route::get('users', [UserController::class, 'get']);
            Route::post('users', [UserController::class, 'index']);
            Route::post('users/store', [UserController::class, 'store']);
            Route::apiResource('users', UserController::class)
                ->except(['index', 'store'])->scoped(['user' => 'uuid']);
        });

        Route::middleware('can:master-role')->group(function () {
            Route::get('roles', [RoleController::class, 'get'])->withoutMiddleware('can:master-role');
            Route::post('roles', [RoleController::class, 'index']);
            Route::post('roles/store', [RoleController::class, 'store']);
            Route::apiResource('roles', RoleController::class)
                ->except(['index', 'store']);
        });
    });

    Route::prefix('kota')->group(function () {
        Route::get('/get', [KotaController::class, 'get']);
        Route::post('', [KotaController::class, 'index']); 
        Route::post('/store', [KotaController::class, 'add']); 
        Route::get('/kota/edit/{uuid}', [KotaController::class, 'edit']); 
        Route::put('/kota/update/{uuid}', [KotaController::class, 'update']);
        Route::delete('/kota/destroy/{uuid}', [KotaController::class, 'destroy']);
    });

    Route::prefix('delivery')->group(function () {
        Route::get('/get', [DeliveryController::class, 'get']);
        Route::get('/delivery/get/{uuid}', [DeliveryController::class, 'get']);
        Route::post('', [DeliveryController::class, 'index']); 
        Route::post('/store', [DeliveryController::class, 'add']); 
        Route::get('/delivery/edit/{uuid}', [DeliveryController::class, 'edit']); 
        Route::put('/delivery/update/{uuid}', [DeliveryController::class, 'update']);
        Route::delete('/delivery/destroy/{uuid}', [DeliveryController::class, 'destroy']);
    });

    Route::prefix('stok')->group(function () {
        Route::get('/get', [StokMobilController::class, 'get']);
        Route::get('/stok/get/{uuid}', [StokMobilController::class, 'get']);
        Route::post('', [StokMobilController::class, 'index']); 
        Route::post('/store', [StokMobilController::class, 'add']); 
        Route::get('/stok/edit/{uuid}', [StokMobilController::class, 'edit']); 
        Route::put('/stok/update/{uuid}', [StokMobilController::class, 'update']);
        Route::delete('/stok/destroy/{uuid}', [StokMobilController::class, 'destroy']);
    });
    Route::prefix('mobil')->group(function () {
        Route::get('/get', [MobilController::class, 'get']);
        Route::get('getkota/{kota_id}', [MobilController::class, 'getMobilByKota']);
        Route::get('/mobil/get/{uuid}', [MobilController::class, 'get']);
        Route::post('', [MobilController::class, 'index']); 
        Route::post('/store', [MobilController::class, 'add']); 
        Route::get('/mobil/edit/{uuid}', [MobilController::class, 'edit']); 
        Route::put('/mobil/update/{uuid}', [MobilController::class, 'update']);
        Route::delete('/mobil/destroy/{uuid}', [MobilController::class, 'destroy']);
    });
    Route::prefix('penyewaan')->group(function () {
        Route::get('/get', [PenyewaanController::class, 'get']);
        Route::get('/penyewaan/get/{uuid}', [PenyewaanController::class, 'get']);
        Route::post('', [PenyewaanController::class, 'index']); 
        Route::post('/store', [PenyewaanController::class, 'add'])->middleware('auth');
        Route::get('/penyewaan/edit/{uuid}', [PenyewaanController::class, 'edit']); 
        Route::put('/penyewaan/update/{uuid}', [PenyewaanController::class, 'update']);
        Route::delete('/penyewaan/destroy/{uuid}', [PenyewaanController::class, 'destroy']);
    });
    
});
