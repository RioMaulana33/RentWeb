<?php

namespace App\Http\Controllers;

use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Penyewaan;

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
                    'finish' => env('MIDTRANS_REDIRECT_URL') . '?payment_status=success&order_id=' . $orderId,
                    'error' => env('MIDTRANS_REDIRECT_URL') . '?payment_status=failure&order_id=' . $orderId,
                    'cancel' => env('MIDTRANS_REDIRECT_URL') . '?payment_status=cancel&order_id=' . $orderId
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
    
    public function handleCallback(Request $request)
    {
        try {
            $notification = json_decode($request->getContent(), true);
            $orderId = $notification['order_id'];
            $transactionStatus = $notification['transaction_status'];
            $fraudStatus = $notification['fraud_status'];

            // Get temporary rental data
            $tempRentalData = session('temp_rental_' . $orderId);

            if (!$tempRentalData) {
                throw new \Exception('Temporary rental data not found');
            }

            if ($transactionStatus == 'capture' && $fraudStatus == 'accept') {
                // Payment success, create rental
                $rentalData = [
                    ...$tempRentalData,
                    'status' => 'pending',
                    'user_id' => auth()->id(),
                    'payment_status' => 'paid',
                    'midtrans_transaction_id' => $notification['transaction_id'],
                    'payment_time' => now()
                ];

                $penyewaan = Penyewaan::create($rentalData);

                // Clear temporary data
                session()->forget('temp_rental_' . $orderId);

                return response()->json([
                    'status' => true,
                    'message' => 'Payment successful and rental created',
                    'data' => $penyewaan
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Payment failed or pending'
            ]);
        } catch (\Exception $e) {
            \Log::error('Midtrans Callback Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
