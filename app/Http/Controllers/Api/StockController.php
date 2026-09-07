<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\AddStockRequest;
use App\Http\Resources\ProductResource;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Stock\ReduceStockRequest;

class StockController extends Controller
{
    public function add(
        AddStockRequest $request,
        StockService $stockService
    ): JsonResponse {
        $product = $stockService->addStock(
            $request->integer('product_id'),
            $request->integer('quantity'),
            $request->input('remarks'),
            $request->user()->id
        );

        return response()->json([
            'message' => 'Stock added successfully.',
            'data' => new ProductResource($product),
        ]);
    }

    public function reduce(
        ReduceStockRequest $request,
        StockService $stockService
    ): JsonResponse {
        $product = $stockService->reduceStock(
            $request->integer('product_id'),
            $request->integer('quantity'),
            $request->input('remarks'),
            $request->user()->id
        );

        return response()->json([
            'message' => 'Stock reduced successfully.',
            'data' => new ProductResource($product),
        ]);
    }
}
