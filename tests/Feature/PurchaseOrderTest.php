<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $staff;
    protected Product $product;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->manager = User::factory()->create([
            'role' => 'manager',
        ]);

        $this->staff = User::factory()->create([
            'role' => 'staff',
        ]);

        $category = Category::factory()->create();

        $this->supplier = Supplier::factory()->create();

        $this->product = Product::factory()->create([
            'category_id' => $category->id,
            'supplier_id' => $this->supplier->id,
            'stock_quantity' => 20,
            'minimum_stock' => 5,
            'purchase_price' => 500,
            'selling_price' => 750,
        ]);
    }

    public function test_manager_can_create_purchase_order(): void
    {
        $this->actingAs($this->manager);

        $response = $this->postJson('/api/purchase-orders', [
            'supplier_id' => $this->supplier->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'price' => 500,
                ],
            ],
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('data.supplier_id', $this->supplier->id)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total_amount', '5000.00');

        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $this->supplier->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'product_id' => $this->product->id,
            'quantity' => 10,
            'price' => 500,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 20,
        ]);
    }
    public function test_staff_cannot_create_purchase_order(): void
    {
        Sanctum::actingAs($this->staff);

        $response = $this->postJson('/api/purchase-orders', [
            'supplier_id' => $this->supplier->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'price' => 500,
                ],
            ],
        ]);

        $response
            ->assertStatus(403)
            ->assertJsonPath(
                'message',
                'You do not have permission to perform this action.'
            );

        $this->assertDatabaseCount('purchase_orders', 0);
        $this->assertDatabaseCount('purchase_order_items', 0);
    }

    public function test_manager_can_list_purchase_orders(): void
    {
        Sanctum::actingAs($this->manager);

        $this->postJson('/api/purchase-orders', [
            'supplier_id' => $this->supplier->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'price' => 500,
                ],
            ],
        ]);

        $this->postJson('/api/purchase-orders', [
            'supplier_id' => $this->supplier->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'price' => 550,
                ],
            ],
        ]);

        $response = $this->getJson('/api/purchase-orders?per_page=1');

        $response
            ->assertStatus(200)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.status', 'pending');
    }

    public function test_manager_can_view_purchase_order(): void
    {
        Sanctum::actingAs($this->manager);

        $createResponse = $this->postJson('/api/purchase-orders', [
            'supplier_id' => $this->supplier->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 4,
                    'price' => 500,
                ],
            ],
        ]);

        $orderId = $createResponse->json('data.id');

        $response = $this->getJson("/api/purchase-orders/{$orderId}");

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.id', $orderId)
            ->assertJsonPath('data.id', $orderId)
            ->assertJsonPath('data.supplier_id', $this->supplier->id)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total_amount', '2000.00')
            ->assertJsonPath('data.items.0.product_id', $this->product->id)
            ->assertJsonPath('data.items.0.quantity', 4)
            ->assertJsonPath('data.items.0.price', '500.00')
            ->assertJsonPath('data.items.0.subtotal', '2000.00');
    }
}
