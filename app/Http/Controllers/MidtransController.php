<?php

namespace App\Http\Controllers;

use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function getToken(Request $request)
    {
        try {
            $orderId = 'RENTAL-' . time();
            
            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int)$request->total_biaya,
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'phone' => auth()->user()->phone ?? '',
                ],
                'item_details' => [
                    [
                        'id' => $request->mobil_id,
                        'price' => (int)$request->total_biaya,
                        'quantity' => 1,
                        'name' => 'Rental Mobil'
                    ]
                ],
                'callbacks' => [
                    'finish' => 'http://192.168.1.7:8000/payment_status=success',
                    'error' => 'http://192.168.1.7:8000/payment_status=failure',
                    'cancel' => 'http://192.168.1.7:8000/payment_status=failure'
                ]
            ];
    
            $snapToken = Snap::getSnapToken($params);
            
            return response()->json([
                'status' => 'success',
                'snap_token' => $snapToken,
                'redirect_url' => "https://app.sandbox.midtrans.com/snap/v2/vtweb/" . $snapToken,
                'order_id' => $orderId
            ]);
        } catch (\Exception $e) {
            \Log::error('Midtrans Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}