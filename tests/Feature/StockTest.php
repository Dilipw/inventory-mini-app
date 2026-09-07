<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StockTest extends TestCase
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
    public function test_manager_can_add_stock(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/stock/add', [
            'product_id' => $this->product->id,
            'quantity' => 10,
            'remarks' => 'New stock received',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'message',
                'Stock added successfully.'
            )
            ->assertJsonPath(
                'data.stock_quantity',
                30
            );

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 30,
        ]);

        $this->assertDatabaseHas('stock_histories', [
            'product_id' => $this->product->id,
            'quantity' => 10,
            'type' => 'IN',
            'remarks' => 'New stock received',
            'created_by' => $this->manager->id,
        ]);
    }

    public function test_manager_can_reduce_stock(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/stock/reduce', [
            'product_id' => $this->product->id,
            'quantity' => 5,
            'remarks' => 'Stock issued',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'message',
                'Stock reduced successfully.'
            )
            ->assertJsonPath(
                'data.stock_quantity',
                15
            );

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 15,
        ]);

        $this->assertDatabaseHas('stock_histories', [
            'product_id' => $this->product->id,
            'quantity' => 5,
            'type' => 'OUT',
            'remarks' => 'Stock issued',
            'created_by' => $this->manager->id,
        ]);
    }

    public function test_manager_cannot_reduce_insufficient_stock(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/stock/reduce', [
            'product_id' => $this->product->id,
            'quantity' => 25,
            'remarks' => 'Should fail',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonPath(
                'message',
                'Insufficient stock.'
            );

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 20,
        ]);

        $this->assertDatabaseCount('stock_histories', 0);
    }

    public function test_staff_cannot_add_stock(): void
    {
        Sanctum::actingAs($this->staff);

        $response = $this->postJson('/api/stock/add', [
            'product_id' => $this->product->id,
            'quantity' => 10,
            'remarks' => 'Unauthorized stock',
        ]);

        $response
            ->assertStatus(403)
            ->assertJsonPath(
                'message',
                'You do not have permission to perform this action.'
            );

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 20,
        ]);

        $this->assertDatabaseCount('stock_histories', 0);
    }

    public function test_staff_cannot_reduce_stock(): void
    {
        Sanctum::actingAs($this->staff);

        $response = $this->postJson('/api/stock/reduce', [
            'product_id' => $this->product->id,
            'quantity' => 5,
            'remarks' => 'Unauthorized stock reduction',
        ]);

        $response
            ->assertStatus(403)
            ->assertJsonPath(
                'message',
                'You do not have permission to perform this action.'
            );

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 20,
        ]);

        $this->assertDatabaseCount('stock_histories', 0);
    }

    public function test_manager_can_view_stock_history(): void
    {
        Sanctum::actingAs($this->manager);

        $this->postJson('/api/stock/add', [
            'product_id' => $this->product->id,
            'quantity' => 10,
            'remarks' => 'Stock received',
        ]);

        $this->postJson('/api/stock/reduce', [
            'product_id' => $this->product->id,
            'quantity' => 5,
            'remarks' => 'Stock issued',
        ]);

        $response = $this->getJson(
            "/api/stock/history?product_id={$this->product->id}&type=IN&per_page=1"
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.product_id', $this->product->id)
            ->assertJsonPath('data.0.quantity', 10)
            ->assertJsonPath('data.0.type', 'IN')
            ->assertJsonPath('data.0.remarks', 'Stock received');
    }


    public function test_manager_can_filter_stock_history_by_out_type(): void
    {
        Sanctum::actingAs($this->manager);

        $this->postJson('/api/stock/add', [
            'product_id' => $this->product->id,
            'quantity' => 10,
            'remarks' => 'Stock received',
        ]);

        $this->postJson('/api/stock/reduce', [
            'product_id' => $this->product->id,
            'quantity' => 5,
            'remarks' => 'Stock issued',
        ]);

        $response = $this->getJson(
            "/api/stock/history?product_id={$this->product->id}&type=OUT"
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.product_id', $this->product->id)
            ->assertJsonPath('data.0.quantity', 5)
            ->assertJsonPath('data.0.type', 'OUT')
            ->assertJsonPath('data.0.remarks', 'Stock issued');
    }

    public function test_unauthenticated_user_cannot_access_stock(): void
    {
        $response = $this->postJson('/api/stock/add', [
            'product_id' => $this->product->id,
            'quantity' => 10,
            'remarks' => 'Unauthorized request',
        ]);

        $response->assertStatus(401);

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 20,
        ]);

        $this->assertDatabaseCount('stock_histories', 0);
    }

    public function test_stock_add_requires_positive_quantity(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/stock/add', [
            'product_id' => $this->product->id,
            'quantity' => 0,
            'remarks' => 'Invalid quantity',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'quantity',
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 20,
        ]);

        $this->assertDatabaseCount('stock_histories', 0);
    }

    public function test_stock_reduce_requires_positive_quantity(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/stock/reduce', [
            'product_id' => $this->product->id,
            'quantity' => 0,
            'remarks' => 'Invalid quantity',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'quantity',
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 20,
        ]);

        $this->assertDatabaseCount('stock_histories', 0);
    }

    public function test_stock_add_requires_existing_product(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/stock/add', [
            'product_id' => 999999,
            'quantity' => 10,
            'remarks' => 'Invalid product',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'product_id',
            ]);

        $this->assertDatabaseCount('stock_histories', 0);
    }
    public function test_stock_reduce_requires_existing_product(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/stock/reduce', [
            'product_id' => 999999,
            'quantity' => 10,
            'remarks' => 'Invalid product',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'product_id',
            ]);

        $this->assertDatabaseCount('stock_histories', 0);
    }

    public function test_stock_history_rejects_invalid_type(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->getJson('/api/stock/history?type=INVALID');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'type',
            ]);
    }

    public function test_stock_history_rejects_invalid_sort_direction(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->getJson('/api/stock/history?sort_direction=INVALID');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'sort_direction',
            ]);
    }


    public function test_stock_history_rejects_invalid_per_page(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->getJson('/api/stock/history?per_page=101');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'per_page',
            ]);
    }
}
