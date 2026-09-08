<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $staff;
    private Category $category;
    private Category $secondCategory;
    private Supplier $supplier;
    private Supplier $secondSupplier;
    private Product $product;

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

        $this->category = Category::factory()->create();
        $this->secondCategory = Category::factory()->create();

        $this->supplier = Supplier::factory()->create();
        $this->secondSupplier = Supplier::factory()->create();

        $this->product = Product::factory()->create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'product_name' => 'Test Product',
            'sku' => 'TEST-001',
            'purchase_price' => 100,
            'selling_price' => 150,
            'stock_quantity' => 20,
            'minimum_stock' => 5,
            'status' => true,
        ]);
    }

    public function test_admin_can_create_product(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/products', [
            'product_name' => 'New Product',
            'sku' => 'TEST-002',
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'purchase_price' => 100,
            'selling_price' => 150,
            'stock_quantity' => 20,
            'minimum_stock' => 5,
            'description' => 'Test product',
            'status' => true,
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('message', 'Product created successfully.')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'product_name',
                    'sku',
                    'category',
                    'supplier',
                    'purchase_price',
                    'selling_price',
                    'stock_quantity',
                    'minimum_stock',
                    'description',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'sku' => 'TEST-002',
            'stock_quantity' => 20,
        ]);
    }

    public function test_manager_can_create_product(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson('/api/products', [
            'product_name' => 'Manager Product',
            'sku' => 'MANAGER-001',
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'purchase_price' => 100,
            'selling_price' => 150,
            'stock_quantity' => 0,
            'minimum_stock' => 5,
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('message', 'Product created successfully.')
            ->assertJsonPath('data.product_name', 'Manager Product')
            ->assertJsonPath('data.sku', 'MANAGER-001');

        $this->assertDatabaseHas('products', [
            'sku' => 'MANAGER-001',
        ]);
    }

    public function test_staff_cannot_create_product(): void
    {
        Sanctum::actingAs($this->staff);

        $response = $this->postJson('/api/products', [
            'product_name' => 'Staff Product',
            'sku' => 'STAFF-001',
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'purchase_price' => 100,
            'selling_price' => 150,
        ]);

        $response
            ->assertStatus(403)
            ->assertJson([
                'message' => 'You do not have permission to perform this action.',
            ]);

        $this->assertDatabaseMissing('products', [
            'sku' => 'STAFF-001',
        ]);
    }

    public function test_product_creation_requires_required_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/products', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'product_name',
                'sku',
                'category_id',
                'supplier_id',
                'purchase_price',
                'selling_price',
            ]);
    }

    public function test_product_creation_rejects_duplicate_sku(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/products', [
            'product_name' => 'Duplicate SKU Product',
            'sku' => $this->product->sku,
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'purchase_price' => 100,
            'selling_price' => 150,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'sku',
            ]);
    }

    public function test_product_creation_requires_existing_category(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/products', [
            'product_name' => 'Invalid Category Product',
            'sku' => 'INVALID-CATEGORY-001',
            'category_id' => 999999,
            'supplier_id' => $this->supplier->id,
            'purchase_price' => 100,
            'selling_price' => 150,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'category_id',
            ]);
    }

    public function test_product_creation_requires_existing_supplier(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/products', [
            'product_name' => 'Invalid Supplier Product',
            'sku' => 'INVALID-SUPPLIER-001',
            'category_id' => $this->category->id,
            'supplier_id' => 999999,
            'purchase_price' => 100,
            'selling_price' => 150,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'supplier_id',
            ]);
    }

    public function test_product_creation_rejects_negative_prices(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/products', [
            'product_name' => 'Negative Price Product',
            'sku' => 'NEGATIVE-PRICE-001',
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'purchase_price' => -100,
            'selling_price' => -150,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'purchase_price',
                'selling_price',
            ]);
    }

    public function test_product_creation_rejects_negative_stock(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/products', [
            'product_name' => 'Negative Stock Product',
            'sku' => 'NEGATIVE-STOCK-001',
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'purchase_price' => 100,
            'selling_price' => 150,
            'stock_quantity' => -1,
            'minimum_stock' => 5,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'stock_quantity',
            ]);
    }

    public function test_product_creation_rejects_negative_minimum_stock(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/products', [
            'product_name' => 'Negative Minimum Stock Product',
            'sku' => 'NEGATIVE-MINIMUM-001',
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'purchase_price' => 100,
            'selling_price' => 150,
            'minimum_stock' => -1,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'minimum_stock',
            ]);
    }

    public function test_authenticated_user_can_list_products(): void
    {
        Sanctum::actingAs($this->staff);

        Product::factory(5)->create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
        ]);

        $response = $this->getJson('/api/products');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    public function test_authenticated_user_can_view_product(): void
    {
        Sanctum::actingAs($this->staff);

        $response = $this->getJson("/api/products/{$this->product->id}");

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.id', $this->product->id)
            ->assertJsonPath('data.product_name', 'Test Product')
            ->assertJsonPath('data.sku', 'TEST-001');
    }

    public function test_product_search_by_name(): void
    {
        Sanctum::actingAs($this->staff);

        Product::factory()->create([
            'product_name' => 'Special Laptop',
            'sku' => 'LAPTOP-001',
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
        ]);

        Product::factory()->create([
            'product_name' => 'Office Chair',
            'sku' => 'CHAIR-001',
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
        ]);

        $response = $this->getJson('/api/products?search=Laptop');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.product_name', 'Special Laptop');
    }

    public function test_product_search_by_sku(): void
    {
        Sanctum::actingAs($this->staff);

        Product::factory()->create([
            'product_name' => 'Laptop',
            'sku' => 'UNIQUE-SKU-999',
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
        ]);

        $response = $this->getJson('/api/products?search=UNIQUE-SKU-999');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sku', 'UNIQUE-SKU-999');
    }

    public function test_products_can_be_filtered_by_category(): void
    {
        Sanctum::actingAs($this->staff);

        Product::factory()->create([
            'category_id' => $this->secondCategory->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'CATEGORY-002',
        ]);

        $response = $this->getJson(
            "/api/products?category_id={$this->secondCategory->id}"
        );

        $response->assertStatus(200);

        foreach ($response->json('data') as $product) {
            $this->assertEquals(
                $this->secondCategory->id,
                $product['category']['id']
            );
        }
    }

    public function test_products_can_be_filtered_by_supplier(): void
    {
        Sanctum::actingAs($this->staff);

        Product::factory()->create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->secondSupplier->id,
            'sku' => 'SUPPLIER-002',
        ]);

        $response = $this->getJson(
            "/api/products?supplier_id={$this->secondSupplier->id}"
        );

        $response->assertStatus(200);

        foreach ($response->json('data') as $product) {
            $this->assertEquals(
                $this->secondSupplier->id,
                $product['supplier']['id']
            );
        }
    }

    public function test_products_can_be_filtered_by_low_stock(): void
    {
        Sanctum::actingAs($this->staff);

        Product::factory()->create([
            'stock_quantity' => 3,
            'minimum_stock' => 5,
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'LOW-STOCK-001',
        ]);

        Product::factory()->create([
            'stock_quantity' => 20,
            'minimum_stock' => 5,
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'NORMAL-STOCK-001',
        ]);

        $response = $this->getJson('/api/products?low_stock=1');

        $response->assertStatus(200);

        foreach ($response->json('data') as $product) {
            $this->assertLessThanOrEqual(
                $product['minimum_stock'],
                $product['stock_quantity']
            );
        }
    }

    public function test_products_can_be_filtered_by_price_range(): void
    {
        Sanctum::actingAs($this->staff);

        Product::factory()->create([
            'selling_price' => 500,
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'PRICE-001',
        ]);

        Product::factory()->create([
            'selling_price' => 2000,
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'PRICE-002',
        ]);

        $response = $this->getJson(
            '/api/products?min_price=400&max_price=1000'
        );

        $response->assertStatus(200);

        foreach ($response->json('data') as $product) {
            $this->assertGreaterThanOrEqual(400, $product['selling_price']);
            $this->assertLessThanOrEqual(1000, $product['selling_price']);
        }
    }

    public function test_products_can_be_filtered_by_stock_range(): void
    {
        Sanctum::actingAs($this->staff);

        Product::factory()->create([
            'stock_quantity' => 10,
            'minimum_stock' => 5,
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'STOCK-RANGE-001',
        ]);

        Product::factory()->create([
            'stock_quantity' => 50,
            'minimum_stock' => 5,
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'STOCK-RANGE-002',
        ]);

        $response = $this->getJson(
            '/api/products?min_stock=5&max_stock=20'
        );

        $response->assertStatus(200);

        foreach ($response->json('data') as $product) {
            $this->assertGreaterThanOrEqual(5, $product['stock_quantity']);
            $this->assertLessThanOrEqual(20, $product['stock_quantity']);
        }
    }

    public function test_products_can_be_filtered_by_status(): void
    {
        Sanctum::actingAs($this->staff);

        Product::factory()->create([
            'status' => false,
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'INACTIVE-001',
        ]);

        $response = $this->getJson('/api/products?status=0');

        $response->assertStatus(200);

        foreach ($response->json('data') as $product) {
            $this->assertFalse($product['status']);
        }
    }

    public function test_products_support_pagination(): void
    {
        Sanctum::actingAs($this->staff);

        Product::factory(20)->create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
        ]);

        $response = $this->getJson('/api/products?per_page=5');

        $response
            ->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.per_page', 5);
    }

    public function test_products_support_sorting(): void
    {
        Sanctum::actingAs($this->staff);

        $response = $this->getJson(
            '/api/products?sort_by=selling_price&sort_direction=asc'
        );

        $response->assertStatus(200);

        $prices = collect($response->json('data'))
            ->pluck('selling_price')
            ->map(fn($price) => (float) $price)
            ->values()
            ->all();

        $sortedPrices = $prices;
        sort($sortedPrices);

        $this->assertSame($sortedPrices, $prices);
    }

    public function test_admin_can_update_product(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson(
            "/api/products/{$this->product->id}",
            [
                'product_name' => 'Updated Product',
                'selling_price' => 200,
                'minimum_stock' => 10,
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath('message', 'Product updated successfully.')
            ->assertJsonPath('data.product_name', 'Updated Product')
            ->assertJsonPath('data.selling_price', '200.00')
            ->assertJsonPath('data.minimum_stock', 10);

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'product_name' => 'Updated Product',
            'selling_price' => 200,
            'minimum_stock' => 10,
        ]);
    }

    public function test_manager_can_update_product(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->putJson(
            "/api/products/{$this->product->id}",
            [
                'product_name' => 'Manager Updated Product',
                'selling_price' => 200,
                'minimum_stock' => 10,
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath('message', 'Product updated successfully.')
            ->assertJsonPath('data.product_name', 'Manager Updated Product')
            ->assertJsonPath('data.selling_price', '200.00')
            ->assertJsonPath('data.minimum_stock', 10);

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'product_name' => 'Manager Updated Product',
            'selling_price' => 200,
            'minimum_stock' => 10,
        ]);
    }
    public function test_staff_cannot_update_product(): void
    {
        Sanctum::actingAs($this->staff);

        $response = $this->putJson(
            "/api/products/{$this->product->id}",
            [
                'product_name' => 'Staff Update',
            ]
        );

        $response
            ->assertStatus(403)
            ->assertJson([
                'message' => 'You do not have permission to perform this action.',
            ]);
    }

    public function test_product_update_cannot_change_stock_quantity(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson(
            "/api/products/{$this->product->id}",
            [
                'product_name' => 'Updated Product',
                'stock_quantity' => 100,
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.stock_quantity', 20);

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_quantity' => 20,
        ]);
    }

    public function test_admin_can_delete_product(): void
    {
        Sanctum::actingAs($this->admin);

        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'DELETE-001',
        ]);

        $response = $this->deleteJson(
            "/api/products/{$product->id}"
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Product deleted successfully.',
            ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_manager_cannot_delete_product(): void
    {
        Sanctum::actingAs($this->manager);

        $response = $this->deleteJson(
            "/api/products/{$this->product->id}"
        );

        $response
            ->assertStatus(403)
            ->assertJson([
                'message' => 'You do not have permission to perform this action.',
            ]);
    }

    public function test_staff_cannot_delete_product(): void
    {
        Sanctum::actingAs($this->staff);

        $response = $this->deleteJson(
            "/api/products/{$this->product->id}"
        );

        $response
            ->assertStatus(403)
            ->assertJson([
                'message' => 'You do not have permission to perform this action.',
            ]);
    }

    public function test_unauthenticated_user_cannot_access_products(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(401);
    }

    public function test_product_not_found_returns_404(): void
    {
        Sanctum::actingAs($this->staff);

        $response = $this->getJson('/api/products/999999');

        $response
            ->assertStatus(404)
            ->assertJson([
                'message' => 'Resource not found.',
            ]);
    }
}
