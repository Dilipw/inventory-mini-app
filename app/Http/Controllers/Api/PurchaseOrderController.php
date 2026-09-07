<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\StorePurchaseOrderRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Services\PurchaseService;
use Illuminate\Http\JsonResponse;

class PurchaseOrderController extends Controller
{
    public function store(
        StorePurchaseOrderRequest $request,
        PurchaseService $purchaseService
    ): JsonResponse {
        $purchaseOrder = $purchaseService->createOrder(
            $request->integer('supplier_id'),
            $request->input('purchase_date'),
            $request->input('items')
        );

        return response()->json([
            'message' => 'Purchase order created successfully.',
            'data' => new PurchaseOrderResource($purchaseOrder),
        ], 201);
    }
}