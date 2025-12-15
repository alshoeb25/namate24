<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CreditPackage;
use App\Models\Wallet;
use App\Models\CreditPurchase;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Razorpay\Api\Api as RazorpayApi;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $wallet = Wallet::with('credit_purchases')->where('user_id', $request->user()->id)->firstOrFail();
        return response()->json($wallet);
    }

    public function packages()
    {
        return response()->json(CreditPackage::all());
    }

    public function buy(Request $request)
    {
        $request->validate(['package_id' => 'required|exists:credit_packages,id']);

        $package = CreditPackage::findOrFail($request->package_id);
        $user = $request->user();

        // get or create wallet
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

        DB::beginTransaction();
        try {
            // create a pending purchase
            $purchase = $wallet->credit_purchases()->create([
                'credits_total' => $package->credits,
                'credits_consumed' => 0,
                'amount_paid' => $package->price,
                'status' => 'pending',
                'expires_at' => $package->validity_days ? now()->addDays($package->validity_days) : null,
            ]);

            // Create Razorpay order
            $rzp = new RazorpayApi(config('services.razorpay.key'), config('services.razorpay.secret'));
            $order = $rzp->order->create([
                'amount' => intval($package->price * 100),
                'currency' => 'INR',
                'receipt' => 'purchase_'.$purchase->id,
                'payment_capture' => 1,
            ]);

            // persist razorpay order id in purchase meta
            $purchase->update(['payment_id' => $order['id']]);

            DB::commit();

            return response()->json([
                'order' => $order,
                'purchase_id' => $purchase->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['error' => 'Could not create purchase'], 500);
        }
    }

    /**
     * Verify razorpay payment signature coming from frontend and finalize purchase.
     */
    public function verify(Request $request, CreditService $creditService)
    {
        $data = $request->validate([
            'purchase_id' => 'required|exists:credit_purchases,id',
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $purchase = CreditPurchase::findOrFail($data['purchase_id']);

        // Ensure this purchase belongs to logged-in user's wallet
        $wallet = $purchase->wallet;
        if ($wallet->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Verify signature: HMAC_SHA256(order_id + "|" + payment_id, secret)
        $payload = $data['razorpay_order_id'] . '|' . $data['razorpay_payment_id'];
        $expectedSig = hash_hmac('sha256', $payload, config('services.razorpay.secret'));

        if (!hash_equals($expectedSig, $data['razorpay_signature'])) {
            return response()->json(['message' => 'Invalid signature'], 422);
        }

        // Mark purchase paid & credit wallet (idempotent)
        if ($purchase->status !== 'paid') {
            $purchase->update([
                'status' => 'paid',
                'purchased_at' => now(),
            ]);

            $creditService->creditWallet($purchase);
        }

        return response()->json(['status' => 'ok']);
    }
}