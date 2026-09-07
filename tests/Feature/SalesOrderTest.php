<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SalesOrderTest extends TestCase
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

    public function test_manager_can_create_sales_order(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/sales-orders', [
            'customer_name' => 'Test Customer',
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'price' => 750,
                ],
            ],
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('data.customer_name', 'Test Customer')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total_amount', '3750.00');

        $this->assertDatabaseHas('sales_orders', [
            'customer_name' => 'Test Customer',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('sales_order_items', [
            'product_id' => $this->product->id,
            'quantity' => 5,
            'price' => 750,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 20,
        ]);
    }

    public function test_manager_can_list_sales_orders(): void
    {
        Sanctum::actingAs($this->manager);

        $this->postJson('/api/sales-orders', [
            'customer_name' => 'Customer One',
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'price' => 750,
                ],
            ],
        ]);

        $this->postJson('/api/sales-orders', [
            'customer_name' => 'Customer Two',
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 3,
                    'price' => 750,
                ],
            ],
        ]);

        $response = $this->getJson('/api/sales-orders?per_page=1');

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.0.customer_name', 'Customer Two')
            ->assertJsonPath('data.0.status', 'pending')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_manager_can_view_sales_order(): void
    {
        Sanctum::actingAs($this->manager);

        $createResponse = $this->postJson('/api/sales-orders', [
            'customer_name' => 'Show Test Customer',
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 4,
                    'price' => 750,
                ],
            ],
        ]);

        $orderId = $createResponse->json('data.id');

        $response = $this->getJson("/api/sales-orders/{$orderId}");

        $response
            ->assertStatus(200)
            ->assertJsonPath('message', 'Sales order fetched successfully.')
            ->assertJsonPath('data.id', $orderId)
            ->assertJsonPath('data.customer_name', 'Show Test Customer')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total_amount', '3000.00')
            ->assertJsonPath('data.items.0.product_id', $this->product->id)
            ->assertJsonPath('data.items.0.quantity', 4)
            ->assertJsonPath('data.items.0.price', '750.00')
            ->assertJsonPath('data.items.0.subtotal', '3000.00');
    }


    public function test_manager_can_update_pending_sales_order(): void
    {
        Sanctum::actingAs($this->manager);

        $createResponse = $this->postJson('/api/sales-orders', [
            'customer_name' => 'Old Customer',
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'price' => 750,
                ],
            ],
        ]);

        $orderId = $createResponse->json('data.id');

        $response = $this->putJson("/api/sales-orders/{$orderId}", [
            'customer_name' => 'Updated Customer',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'price' => 800,
                ],
            ],
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('message', 'Sales order updated successfully.')
            ->assertJsonPath('data.customer_name', 'Updated Customer')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total_amount', '4000.00')
            ->assertJsonPath('data.items.0.quantity', 5)
            ->assertJsonPath('data.items.0.price', '800.00')
            ->assertJsonPath('data.items.0.subtotal', '4000.00');

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 20,
        ]);
    }

    public function test_staff_cannot_update_sales_order(): void
    {
        Sanctum::actingAs($this->manager);

        $createResponse = $this->postJson('/api/sales-orders', [
            'customer_name' => 'Authorization Test',
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'price' => 750,
                ],
            ],
        ]);

        $orderId = $createResponse->json('data.id');

        Sanctum::actingAs($this->staff);

        $response = $this->putJson("/api/sales-orders/{$orderId}", [
            'customer_name' => 'Should Not Update',
        ]);

        $response
            ->assertStatus(403)
            ->assertJsonPath(
                'message',
                'You do not have permission to perform this action.'
            );

        $this->assertDatabaseHas('sales_orders', [
            'id' => $orderId,
            'customer_name' => 'Authorization Test',
            'status' => 'pending',
        ]);
    }

    public function test_manager_can_complete_sales_order_and_reduce_stock(): void
    {
        Sanctum::actingAs($this->manager);

        $createResponse = $this->postJson('/api/sales-orders', [
            'customer_name' => 'Completion Test Customer',
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'price' => 750,
                ],
            ],
        ]);

        $orderId = $createResponse->json('data.id');

        $response = $this->postJson(
            "/api/sales-orders/{$orderId}/complete"
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'message',
                'Sales order completed successfully.'
            )
            ->assertJsonPath('data.id', $orderId)
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('sales_orders', [
            'id' => $orderId,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 15,
        ]);

        $this->assertDatabaseHas('stock_histories', [
            'product_id' => $this->product->id,
            'quantity' => 5,
            'type' => 'OUT',
            'remarks' => "Sales Order #{$orderId}",
            'created_by' => $this->manager->id,
        ]);
    }

    public function test_completed_sales_order_cannot_be_completed_again(): void
    {
        Sanctum::actingAs($this->manager);

        $createResponse = $this->postJson('/api/sales-orders', [
            'customer_name' => 'Duplicate Completion Test',
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'price' => 750,
                ],
            ],
        ]);

        $orderId = $createResponse->json('data.id');

        $this->postJson("/api/sales-orders/{$orderId}/complete")
            ->assertStatus(200);

        $response = $this->postJson(
            "/api/sales-orders/{$orderId}/complete"
        );

        $response
            ->assertStatus(409)
            ->assertJsonPath(
                'message',
                'Completed sales order cannot be updated.'
            );

        $this->assertDatabaseHas('sales_orders', [
            'id' => $orderId,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 15,
        ]);

        $this->assertDatabaseCount('stock_histories', 1);
    }

    public function test_sales_order_completion_fails_when_stock_is_insufficient(): void
    {
        Sanctum::actingAs($this->manager);

        $createResponse = $this->postJson('/api/sales-orders', [
            'customer_name' => 'Insufficient Stock Test',
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 25,
                    'price' => 750,
                ],
            ],
        ]);

        $orderId = $createResponse->json('data.id');

        $response = $this->postJson(
            "/api/sales-orders/{$orderId}/complete"
        );

        $response
            ->assertStatus(422)
            ->assertJsonPath(
                'message',
                'Insufficient stock.'
            );

        $this->assertDatabaseHas('sales_orders', [
            'id' => $orderId,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 20,
        ]);

        $this->assertDatabaseCount('stock_histories', 0);
    }

    public function test_sales_order_requires_positive_quantity(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/sales-orders', [
            'customer_name' => 'Validation Test',
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 0,
                    'price' => 750,
                ],
            ],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'items.0.quantity',
            ]);

        $this->assertDatabaseCount('sales_orders', 0);
        $this->assertDatabaseCount('sales_order_items', 0);
    }

    public function test_sales_order_requires_existing_product(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/sales-orders', [
            'customer_name' => 'Validation Test',
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => 999999,
                    'quantity' => 2,
                    'price' => 750,
                ],
            ],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'items.0.product_id',
            ]);

        $this->assertDatabaseCount('sales_orders', 0);
        $this->assertDatabaseCount('sales_order_items', 0);
    }

    public function test_sales_order_rejects_negative_price(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/sales-orders', [
            'customer_name' => 'Validation Test',
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'price' => -100,
                ],
            ],
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'items.0.price',
            ]);

        $this->assertDatabaseCount('sales_orders', 0);
        $this->assertDatabaseCount('sales_order_items', 0);
    }

    public function test_sales_order_requires_required_fields(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/sales-orders', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'customer_name',
                'order_date',
                'items',
            ]);

        $this->assertDatabaseCount('sales_orders', 0);
        $this->assertDatabaseCount('sales_order_items', 0);
    }

    public function test_staff_cannot_complete_sales_order(): void
    {
        Sanctum::actingAs($this->manager);

        $createResponse = $this->postJson('/api/sales-orders', [
            'customer_name' => 'Staff Authorization Test',
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'price' => 750,
                ],
            ],
        ]);

        $orderId = $createResponse->json('data.id');

        Sanctum::actingAs($this->staff);

        $response = $this->postJson(
            "/api/sales-orders/{$orderId}/complete"
        );

        $response
            ->assertStatus(403)
            ->assertJsonPath(
                'message',
                'You do not have permission to perform this action.'
            );

        $this->assertDatabaseHas('sales_orders', [
            'id' => $orderId,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 20,
        ]);

        $this->assertDatabaseCount('stock_histories', 0);
    }

    public function test_unauthenticated_user_cannot_access_sales_orders(): void
    {
        $response = $this->getJson('/api/sales-orders');

        $response->assertStatus(401);
    }

    public function test_sales_order_not_found_returns_404(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->getJson('/api/sales-orders/999999');

        $response
            ->assertStatus(404)
            ->assertJson([
                'message' => 'Resource not found.',
            ]);
    }
}
