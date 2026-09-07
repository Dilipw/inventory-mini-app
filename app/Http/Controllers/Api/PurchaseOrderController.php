<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\StorePurchaseOrderRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Services\PurchaseService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Purchase\IndexPurchaseOrderRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Models\PurchaseOrder;

class PurchaseOrderController extends Controller
{

    public function index(
        IndexPurchaseOrderRequest $request
    ): AnonymousResourceCollection {
        $filters = $request->validated();

        $query = PurchaseOrder::query()
            ->with([
                'supplier',
                'items.product',
            ]);

        if (isset($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('purchase_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('purchase_date', '<=', $filters['date_to']);
        }

        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $query->orderBy('purchase_date', $sortDirection)
            ->orderBy('id', $sortDirection);

        $perPage = $filters['per_page'] ?? 15;

        return PurchaseOrderResource::collection(
            $query->paginate($perPage)
        );
    }

    public function show(
        PurchaseOrder $purchaseOrder
    ): PurchaseOrderResource {
        $purchaseOrder->load([
            'supplier',
            'items.product',
        ]);

        return new PurchaseOrderResource($purchaseOrder);
    }

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
