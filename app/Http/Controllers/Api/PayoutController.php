<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', PayoutRequest::class);
        return response()->json(PayoutRequest::with('tutor.user')->paginate(30));
    }

    public function store(Request $r)
    {
        $r->validate(['amount'=>'required|numeric|min:0.01','notes'=>'nullable|string']);
        $user = $r->user();
        $tutor = $user->tutor;
        if (!$tutor) abort(403);
        $payout = PayoutRequest::create(['tutor_id'=>$tutor->id,'amount'=>$r->amount,'notes'=>$r->notes,'status'=>'pending']);
        return response()->json($payout,201);
    }

    public function update(Request $r, PayoutRequest $payout)
    {
        $this->authorize('update', $payout);
        $r->validate(['status'=>'required|in:pending,paid,rejected','notes'=>'nullable|string']);
        $payout->update($r->only(['status','notes']));
        return response()->json($payout);
    }
}