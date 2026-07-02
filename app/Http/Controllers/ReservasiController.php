<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;

class ReservasiController extends Controller
{
    public function index()
    {
        return view('reservasi');
    }

    public function createPayment(Request $request)
    {
        // Validasi input
        $request->validate([
            'total' => 'required|numeric|min:1',
            'nama'  => 'required|string|max:255',
            'hp'    => 'required|string|max:20',
        ]);

        // Konfigurasi Midtrans
        Config::$serverKey    = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false; // Ganti true jika sudah production
        Config::$isSanitized  = true;
        Config::$is3ds        = true;

        $orderId = 'TEMPATIN-' . time() . '-' . rand(100, 999);

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => (int) $request->total,
            ],
            'customer_details' => [
                'first_name' => $request->nama,
                'phone'      => $request->hp,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            return response()->json(['token' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}