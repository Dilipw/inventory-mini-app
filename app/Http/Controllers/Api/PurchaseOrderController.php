<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\StorePurchaseOrderRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Services\PurchaseService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function complete(
        int $purchaseOrder,
        PurchaseService $purchaseService,
        StockService $stockService,
        Request $request
    ): JsonResponse {
        $purchaseOrder = $purchaseService->completeOrder(
            $purchaseOrder,
            $request->user()->id,
            $stockService
        );

        return response()->json([
            'message' => 'Purchase order completed successfully.',
            'data' => new PurchaseOrderResource($purchaseOrder),
        ]);
    }
}