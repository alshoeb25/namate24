<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CreditPackage;
use Illuminate\Http\Request;

class CreditPackageController extends Controller
{
    public function index() { return response()->json(CreditPackage::all()); }

    public function store(Request $r) {
        $p = CreditPackage::create($r->validate(['name'=>'required','credits'=>'required|int','price'=>'required|numeric','validity_days'=>'nullable|int']));
        return response()->json($p,201);
    }

    public function update(Request $r, CreditPackage $creditPackage) {
        $creditPackage->update($r->all());
        return response()->json($creditPackage);
    }

    public function destroy(CreditPackage $creditPackage) {
        $creditPackage->delete();
        return response()->json(['deleted'=>true]);
    }
}