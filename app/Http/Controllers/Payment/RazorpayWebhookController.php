<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessRazorpayPaymentJob;

class RazorpayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');

        $secret = config('services.razorpay.webhook_secret');

        if (!hash_equals($signature, hash_hmac('sha256', $payload, $secret))) {
            Log::warning('Razorpay webhook signature mismatch', ['header' => $signature]);
            return response()->json(['status' => 'invalid signature'], 400);
        }

        $data = $request->all();
        ProcessRazorpayPaymentJob::dispatch($data);

        return response()->json(['status' => 'received']);
    }
}