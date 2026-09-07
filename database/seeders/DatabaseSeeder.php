<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use App\Services\SalesService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'role' => 'manager',
        ]);

        User::factory()->create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'role' => 'staff',
        ]);

        $categories = collect([
            ['category_name' => 'Grocery', 'description' => 'Daily grocery and food essentials'],
            ['category_name' => 'Beverages', 'description' => 'Soft drinks, juices and beverages'],
            ['category_name' => 'Personal Care', 'description' => 'Personal hygiene and care products'],
            ['category_name' => 'Household', 'description' => 'Household cleaning and utility products'],
            ['category_name' => 'Snacks', 'description' => 'Biscuits, namkeen and packaged snacks'],
        ])->map(fn (array $category) => Category::create([
            ...$category,
            'status' => true,
        ]));

        $suppliers = collect([
            [
                'supplier_name' => 'Shree Ganesh Distributors',
                'email' => 'ganesh@example.com',
                'phone' => '9876543210',
                'address' => 'Market Yard, Pune, Maharashtra',
                'gst_number' => '27ABCDE1234F1Z5',
            ],
            [
                'supplier_name' => 'Sai Wholesale Mart',
                'email' => 'sai@example.com',
                'phone' => '9876543211',
                'address' => 'MIDC Area, Nashik, Maharashtra',
                'gst_number' => '27BCDEF2345G1Z6',
            ],
            [
                'supplier_name' => 'Om Traders',
                'email' => 'om@example.com',
                'phone' => '9876543212',
                'address' => 'Market Road, Nagpur, Maharashtra',
                'gst_number' => '27CDEFG3456H1Z7',
            ],
            [
                'supplier_name' => 'Krishna Enterprises',
                'email' => 'krishna@example.com',
                'phone' => '9876543213',
                'address' => 'Main Market, Aurangabad, Maharashtra',
                'gst_number' => '27DEFGH4567J1Z8',
            ],
            [
                'supplier_name' => 'Maharashtra Wholesale Mart',
                'email' => 'maharashtra@example.com',
                'phone' => '9876543214',
                'address' => 'Wholesale Market, Mumbai, Maharashtra',
                'gst_number' => '27EFGHI5678K1Z9',
            ],
        ])->map(fn (array $supplier) => Supplier::create($supplier));

        $products = collect([
            [
                'product_name' => 'Tata Salt 1kg',
                'sku' => 'GRC-SALT-001',
                'category_id' => $categories->get(0)->id,
                'supplier_id' => $suppliers->get(0)->id,
                'purchase_price' => 22.00,
                'selling_price' => 28.00,
                'minimum_stock' => 20,
                'description' => 'Iodized packaged salt 1kg',
            ],
            [
                'product_name' => 'Aashirvaad Atta 5kg',
                'sku' => 'GRC-ATTA-001',
                'category_id' => $categories->get(0)->id,
                'supplier_id' => $suppliers->get(0)->id,
                'purchase_price' => 245.00,
                'selling_price' => 285.00,
                'minimum_stock' => 10,
                'description' => 'Whole wheat flour 5kg pack',
            ],
            [
                'product_name' => 'Fortune Sunflower Oil 1L',
                'sku' => 'GRC-OIL-001',
                'category_id' => $categories->get(0)->id,
                'supplier_id' => $suppliers->get(1)->id,
                'purchase_price' => 105.00,
                'selling_price' => 125.00,
                'minimum_stock' => 15,
                'description' => 'Refined sunflower cooking oil',
            ],
            [
                'product_name' => 'Thums Up 750ml',
                'sku' => 'BEV-THM-001',
                'category_id' => $categories->get(1)->id,
                'supplier_id' => $suppliers->get(2)->id,
                'purchase_price' => 32.00,
                'selling_price' => 40.00,
                'minimum_stock' => 24,
                'description' => 'Carbonated soft drink 750ml',
            ],
            [
                'product_name' => 'Tata Glucose Biscuits 800g',
                'sku' => 'SNK-BIS-001',
                'category_id' => $categories->get(4)->id,
                'supplier_id' => $suppliers->get(2)->id,
                'purchase_price' => 55.00,
                'selling_price' => 65.00,
                'minimum_stock' => 20,
                'description' => 'Packaged glucose biscuits',
            ],
            [
                'product_name' => 'Dettol Liquid 250ml',
                'sku' => 'PER-DET-001',
                'category_id' => $categories->get(2)->id,
                'supplier_id' => $suppliers->get(3)->id,
                'purchase_price' => 72.00,
                'selling_price' => 88.00,
                'minimum_stock' => 10,
                'description' => 'Antiseptic liquid 250ml',
            ],
            [
                'product_name' => 'Surf Excel Matic 2kg',
                'sku' => 'HOU-SUR-001',
                'category_id' => $categories->get(3)->id,
                'supplier_id' => $suppliers->get(3)->id,
                'purchase_price' => 285.00,
                'selling_price' => 335.00,
                'minimum_stock' => 8,
                'description' => 'Laundry detergent powder',
            ],
            [
                'product_name' => 'Kurkure Masala Munch 90g',
                'sku' => 'SNK-KUR-001',
                'category_id' => $categories->get(4)->id,
                'supplier_id' => $suppliers->get(2)->id,
                'purchase_price' => 18.00,
                'selling_price' => 20.00,
                'minimum_stock' => 30,
                'description' => 'Masala flavoured packaged snack',
            ],
            [
                'product_name' => 'Parle-G Biscuits 800g',
                'sku' => 'SNK-PAR-001',
                'category_id' => $categories->get(4)->id,
                'supplier_id' => $suppliers->get(0)->id,
                'purchase_price' => 48.00,
                'selling_price' => 58.00,
                'minimum_stock' => 20,
                'description' => 'Classic glucose biscuits',
            ],
            [
                'product_name' => 'Colgate Strong Teeth 200g',
                'sku' => 'PER-COL-001',
                'category_id' => $categories->get(2)->id,
                'supplier_id' => $suppliers->get(3)->id,
                'purchase_price' => 92.00,
                'selling_price' => 110.00,
                'minimum_stock' => 12,
                'description' => 'Fluoride toothpaste 200g',
            ],
        ])->map(fn (array $product) => Product::create([
            ...$product,
            'stock_quantity' => 0,
            'status' => true,
        ]));

        $purchaseService = app(PurchaseService::class);
        $salesService = app(SalesService::class);

        $completedPurchase = $purchaseService->createOrder(
            $suppliers->get(0)->id,
            now()->subDays(5)->toDateString(),
            [
                [
                    'product_id' => $products->get(0)->id,
                    'quantity' => 100,
                    'price' => 22.00,
                ],
                [
                    'product_id' => $products->get(1)->id,
                    'quantity' => 50,
                    'price' => 245.00,
                ],
                [
                    'product_id' => $products->get(8)->id,
                    'quantity' => 80,
                    'price' => 48.00,
                ],
            ]
        );

        $purchaseService->completeOrder(
            $completedPurchase->id,
            $admin->id,
            app(\App\Services\StockService::class)
        );

        $purchaseService->createOrder(
            $suppliers->get(1)->id,
            now()->toDateString(),
            [
                [
                    'product_id' => $products->get(2)->id,
                    'quantity' => 60,
                    'price' => 105.00,
                ],
                [
                    'product_id' => $products->get(5)->id,
                    'quantity' => 40,
                    'price' => 72.00,
                ],
            ]
        );

        $completedSale = $salesService->createOrder(
            'Rahul Patil',
            now()->subDays(2)->toDateString(),
            [
                [
                    'product_id' => $products->get(0)->id,
                    'quantity' => 12,
                    'price' => 28.00,
                ],
                [
                    'product_id' => $products->get(1)->id,
                    'quantity' => 5,
                    'price' => 285.00,
                ],
            ]
        );

        $salesService->completeOrder($completedSale, $admin->id);

        $salesService->createOrder(
            'Shree Sai General Store',
            now()->toDateString(),
            [
                [
                    'product_id' => $products->get(8)->id,
                    'quantity' => 10,
                    'price' => 58.00,
                ],
            ]
        );
    }
}