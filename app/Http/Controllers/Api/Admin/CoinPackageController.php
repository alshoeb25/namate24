<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoinPackage;
use Illuminate\Http\Request;

class CoinPackageController extends Controller
{
    public function index()
    {
        return response()->json(
            CoinPackage::orderBy('sort_order')
                ->orderBy('price')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'coins' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'bonus_coins' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_popular' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $pkg = CoinPackage::create(array_merge([
            'bonus_coins' => $data['bonus_coins'] ?? 0,
            'is_popular' => $data['is_popular'] ?? false,
            'is_active' => $data['is_active'] ?? true,
        ], $data));

        return response()->json($pkg, 201);
    }

    public function update(Request $request, CoinPackage $coinPackage)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'coins' => 'sometimes|integer|min:1',
            'price' => 'sometimes|numeric|min:0',
            'bonus_coins' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'is_popular' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $coinPackage->update($data);
        return response()->json($coinPackage);
    }

    public function destroy(CoinPackage $coinPackage)
    {
        $coinPackage->delete();
        return response()->json(['deleted' => true]);
    }

    public function togglePopular(CoinPackage $coinPackage)
    {
        $coinPackage->update(['is_popular' => !$coinPackage->is_popular]);
        return response()->json($coinPackage);
    }

    public function toggleActive(CoinPackage $coinPackage)
    {
        $coinPackage->update(['is_active' => !$coinPackage->is_active]);
        return response()->json($coinPackage);
    }
}
