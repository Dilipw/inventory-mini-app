<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreSalesOrderRequest;
use App\Http\Resources\SalesOrderResource;
use App\Services\SalesService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Sales\IndexSalesOrderRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Models\SalesOrder;
use App\Http\Requests\Sales\UpdateSalesOrderRequest;
use Illuminate\Http\Request;


class SalesOrderController extends Controller
{
    public function index(
        IndexSalesOrderRequest $request
    ): AnonymousResourceCollection {
        $filters = $request->validated();

        $query = SalesOrder::query()
            ->with([
                'items.product',
            ]);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $query->orderBy('created_at', $sortDirection)
    ->orderBy('id', $sortDirection);

        $perPage = $filters['per_page'] ?? 15;

        return SalesOrderResource::collection(
            $query->paginate($perPage)
        );
    }

    public function show(SalesOrder $salesOrder): JsonResponse
    {
        $salesOrder->load([
            'items.product',
        ]);

        return response()->json([
            'message' => 'Sales order fetched successfully.',
            'data' => new SalesOrderResource($salesOrder),
        ]);
    }

    public function store(
        StoreSalesOrderRequest $request,
        SalesService $salesService
    ): JsonResponse {
        $salesOrder = $salesService->createOrder(
            $request->input('customer_name'),
            $request->input('order_date'),
            $request->input('items')
        );

        return response()->json([
            'message' => 'Sales order created successfully.',
            'data' => new SalesOrderResource($salesOrder),
        ], 201);
    }

    public function update(
        UpdateSalesOrderRequest $request,
        SalesOrder $salesOrder,
        SalesService $salesService
    ): JsonResponse {
        $salesOrder = $salesService->updateOrder(
            $salesOrder,
            $request->input('customer_name'),
            $request->input('order_date'),
            $request->input('items')
        );

        return response()->json([
            'message' => 'Sales order updated successfully.',
            'data' => new SalesOrderResource($salesOrder),
        ]);
    }


    public function complete(
        Request $request,
        SalesOrder $salesOrder,
        SalesService $salesService
    ): JsonResponse {
        $salesOrder = $salesService->completeOrder(
            $salesOrder,
            $request->user()->id
        );

        return response()->json([
            'message' => 'Sales order completed successfully.',
            'data' => new SalesOrderResource($salesOrder),
        ]);
    }
}
