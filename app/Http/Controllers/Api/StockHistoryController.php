<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stock\IndexStockHistoryRequest;
use App\Http\Resources\StockHistoryResource;
use App\Models\StockHistory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockHistoryController extends Controller
{
    public function index(
        IndexStockHistoryRequest $request
    ): AnonymousResourceCollection {
        $filters = $request->validated();

        $query = StockHistory::query()
            ->with([
                'product:id,product_name,sku',
                'creator:id,name',
            ]);

        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $query->orderBy('created_at', $sortDirection);

        $perPage = $filters['per_page'] ?? 15;

        return StockHistoryResource::collection(
            $query->paginate($perPage)
        );
    }
}