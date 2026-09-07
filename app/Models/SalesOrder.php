<?php

namespace App\Models;

use Database\Factories\SalesOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'customer_name',
    'order_date',
    'total_amount',
    'status',
])]
class SalesOrder extends Model
{
    /** @use HasFactory<SalesOrderFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }
}