<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreSalesOrderRequest;
use App\Http\Resources\SalesOrderResource;
use App\Services\SalesService;
use Illuminate\Http\JsonResponse;

class SalesOrderController extends Controller
{
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
}