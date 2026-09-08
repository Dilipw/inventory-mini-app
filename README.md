# Inventory Management System API

A REST API based Inventory Management System built using Laravel and PHP.

This backend assignment focuses on clean API design, authentication, role-based authorization, CRUD operations, stock management, purchase and sales workflows, validation, database relationships, transactions, and query optimization.

## Tech Stack

- PHP 8.3+
- Laravel 13
- Laravel Sanctum
- SQLite for local development
- MySQL-compatible database structure
- PHPUnit / Pest test runner
- RESTful JSON APIs

## Features

### Authentication
- Register
- Login
- Logout
- Profile
- Change password
- Sanctum bearer-token authentication

### Role-Based Access Control
- Admin, Manager and Staff roles
- Custom role middleware for authorization

### Inventory
- Category CRUD
- Supplier CRUD
- Product CRUD
- Stock add/reduce
- Stock history
- Low-stock detection
- Product search and filtering
- Pagination and sorting

### Purchase Orders
- Create purchase orders
- View purchase orders
- Complete purchase orders
- Automatically increase stock on completion
- Prevent duplicate completion

### Sales Orders
- Create sales orders
- View sales orders
- Update pending sales orders
- Complete sales orders
- Automatically reduce stock on completion
- Prevent sales when stock is insufficient
- Prevent duplicate completion

# Installation

## Requirements

- PHP 8.3+
- Composer
- SQLite or MySQL

## Setup

```bash
composer install
```

Create `.env` from `.env.example` and generate the application key:

```bash
php artisan key:generate
```

For Windows:

```bash
copy .env.example .env
php artisan key:generate
```

## Database

For local development, use SQLite.

Create:

```text
database/database.sqlite
```

Set:

```env
DB_CONNECTION=sqlite
```

For MySQL, configure the normal `DB_*` variables in `.env`.

## Migrations and Demo Data

```bash
php artisan migrate:fresh --seed
```

The seeder creates demo users, categories, suppliers, products, purchase orders, sales orders, and stock history.

## Start Server

```bash
php artisan serve
```

Default API base URL:

```text
http://127.0.0.1:8000/api
```

## Demo Credentials

| Role | Email | Password |
|---|---|---|
| Admin | admin@example.com | password |
| Manager | manager@example.com | password |
| Staff | staff@example.com | password |

These credentials are for local/demo use only.

> **Note on self-registration:** Users who sign up through `POST /api/register` are assigned the `staff` role by default. Admin and Manager accounts are created only through the seeder or by an existing Admin — a new user cannot make themselves Admin or Manager through the public registration API.

# API Documentation

All protected endpoints require:

```http
Accept: application/json
Authorization: Bearer <sanctum-token>
```

## 1. Authentication

### Register

```http
POST /api/register
```

```json
{
    "name": "Test User",
    "email": "test@example.com",
    "password": "Password@123",
    "password_confirmation": "Password@123"
}
```

A newly registered user is given the `staff` role by default.

### Login

```http
POST /api/login
```

```json
{
    "email": "admin@example.com",
    "password": "password"
}
```

A successful login returns a Sanctum token. Use that token for protected APIs.

### Profile

```http
GET /api/profile
```

### Logout

```http
POST /api/logout
```

### Change Password

```http
PUT /api/change-password
```

```json
{
    "current_password": "password",
    "password": "Password@123",
    "password_confirmation": "Password@123"
}
```

Changing the password revokes existing Sanctum tokens. Please login again after this to get a fresh token.

## Role-Based Access Control

| Role | Access |
|---|---|
| Admin | Full access to every module |
| Manager | Full access to stock operations and purchase/sales order workflows (create, view, update, complete). Category, Supplier and Product master data can only be created, updated or deleted by Admin. |
| Staff | Read-only access to all modules |

Authorization is handled using custom role middleware, applied per route.

**Why master data is Admin-only:** Category, Supplier and Product records are the master data that everything else in the system depends on (stock, purchase orders and sales orders all reference them). Keeping their creation, editing and deletion restricted to Admin protects the reliability of this master data, while Manager retains full ability to run day-to-day operations — adding stock, and creating and completing purchase/sales orders — without needing Admin involvement for every transaction.

A quick summary of what each role can actually do, module by module:

| Module | Admin | Manager | Staff |
|---|---|---|---|
| Categories (view) | ✅ | ✅ | ✅ |
| Categories (create/update/delete) | ✅ | ❌ | ❌ |
| Suppliers (view) | ✅ | ✅ | ✅ |
| Suppliers (create/update/delete) | ✅ | ❌ | ❌ |
| Products (view) | ✅ | ✅ | ✅ |
| Products (create/update/delete) | ✅ | ❌ | ❌ |
| Stock (add/reduce) | ✅ | ✅ | ❌ |
| Stock history (view) | ✅ | ✅ | ✅ |
| Purchase Orders (create/complete) | ✅ | ✅ | ❌ |
| Purchase Orders (view) | ✅ | ✅ | ✅ |
| Sales Orders (create/update/complete) | ✅ | ✅ | ❌ |
| Sales Orders (view) | ✅ | ✅ | ✅ |

## 2. Categories

| Method | Endpoint | Access |
|---|---|---|
| GET | `/api/categories` | Admin, Manager, Staff |
| GET | `/api/categories/{category}` | Admin, Manager, Staff |
| POST | `/api/categories` | Admin |
| PUT | `/api/categories/{category}` | Admin |
| DELETE | `/api/categories/{category}` | Admin |

### Create

```http
POST /api/categories
```

```json
{
    "category_name": "Dairy Products",
    "description": "Milk and dairy products",
    "status": true
}
```

## 3. Suppliers

| Method | Endpoint | Access |
|---|---|---|
| GET | `/api/suppliers` | Admin, Manager, Staff |
| GET | `/api/suppliers/{supplier}` | Admin, Manager, Staff |
| POST | `/api/suppliers` | Admin |
| PUT | `/api/suppliers/{supplier}` | Admin |
| DELETE | `/api/suppliers/{supplier}` | Admin |

### Create

```http
POST /api/suppliers
```

```json
{
    "supplier_name": "New Wholesale Supplier",
    "email": "supplier@example.com",
    "phone": "9876501234",
    "address": "Market Yard, Pune, Maharashtra",
    "gst_number": "27XYZAB1234C1Z5"
}
```

## 4. Products

| Method | Endpoint | Access |
|---|---|---|
| GET | `/api/products` | Admin, Manager, Staff |
| GET | `/api/products/{product}` | Admin, Manager, Staff |
| POST | `/api/products` | Admin |
| PUT | `/api/products/{product}` | Admin |
| DELETE | `/api/products/{product}` | Admin |

### Create

```http
POST /api/products
```

```json
{
    "product_name": "Test Rice 5kg",
    "sku": "GRC-RICE-TEST-001",
    "category_id": 1,
    "supplier_id": 1,
    "purchase_price": 250,
    "selling_price": 290,
    "stock_quantity": 0,
    "minimum_stock": 10,
    "description": "Test rice product",
    "status": true
}
```

### Search and Filters

Search by name or SKU:

```http
GET /api/products?search=Tata
```

Category:

```http
GET /api/products?category_id=1
```

Supplier:

```http
GET /api/products?supplier_id=1
```

Low stock:

```http
GET /api/products?low_stock=true
```

Price range:

```http
GET /api/products?min_price=50&max_price=500
```

Stock range:

```http
GET /api/products?min_stock=10&max_stock=100
```

Sorting and pagination:

```http
GET /api/products?sort_by=selling_price&sort_direction=asc&per_page=10
```

The API uses an allowlist for sorting fields, so only approved column names can be used for `sort_by`.

> **Note:** Product stock is not changed through the normal product update API. Stock is managed only through the Stock module (`/api/stock/add`, `/api/stock/reduce`) and through purchase/sales order completion. This keeps every stock change auditable through stock history.

## 5. Stock

| Method | Endpoint | Access |
|---|---|---|
| POST | `/api/stock/add` | Admin, Manager |
| POST | `/api/stock/reduce` | Admin, Manager |
| GET | `/api/stock/history` | Admin, Manager, Staff |

### Add Stock

```http
POST /api/stock/add
```

```json
{
    "product_id": 1,
    "quantity": 20,
    "remarks": "Initial stock"
}
```

### Reduce Stock

```http
POST /api/stock/reduce
```

```json
{
    "product_id": 1,
    "quantity": 5,
    "remarks": "Manual stock reduction"
}
```

The API prevents stock from going below zero.

### History

```http
GET /api/stock/history
```

Examples:

```http
GET /api/stock/history?product_id=1
GET /api/stock/history?type=IN
```

### Stock History Fields

Each stock movement record stores:

- `product_id` — the product that was affected
- `quantity` — the quantity moved
- `type` — either `IN` or `OUT`
- `remarks` — an optional note explaining the movement
- `created_by` — the ID of the user who performed the operation
- `created_at` — the time the movement happened

## 6. Purchase Orders

| Method | Endpoint | Access |
|---|---|---|
| POST | `/api/purchase-orders` | Admin, Manager |
| GET | `/api/purchase-orders` | Admin, Manager, Staff |
| GET | `/api/purchase-orders/{purchaseOrder}` | Admin, Manager, Staff |
| POST | `/api/purchase-orders/{purchaseOrder}/complete` | Admin, Manager |

### Create

```http
POST /api/purchase-orders
```

```json
{
    "supplier_id": 1,
    "purchase_date": "2026-09-08",
    "items": [
        {
            "product_id": 1,
            "quantity": 25,
            "price": 22
        },
        {
            "product_id": 2,
            "quantity": 10,
            "price": 245
        }
    ]
}
```

> **Total Amount:** `total_amount` is always calculated on the server from the order items, as `quantity × price` summed across all items. The client should not send `total_amount` in the request — even if it is sent, the server value is what gets saved, so the amount always matches the actual items on the order.

### Complete

```http
POST /api/purchase-orders/{purchaseOrder}/complete
```

On completion:
1. Product stock increases by the ordered quantity, for each item.
2. An `IN` stock history record is created for each item.
3. Order status changes from `pending` to `completed`.
4. The same order cannot be completed again — trying to complete an already-completed order is rejected.

## 7. Sales Orders

| Method | Endpoint | Access |
|---|---|---|
| POST | `/api/sales-orders` | Admin, Manager |
| GET | `/api/sales-orders` | Admin, Manager, Staff |
| GET | `/api/sales-orders/{salesOrder}` | Admin, Manager, Staff |
| PUT | `/api/sales-orders/{salesOrder}` | Admin, Manager |
| POST | `/api/sales-orders/{salesOrder}/complete` | Admin, Manager |

### Create

```http
POST /api/sales-orders
```

```json
{
    "customer_name": "Rahul Patil",
    "order_date": "2026-09-08",
    "items": [
        {
            "product_id": 1,
            "quantity": 2,
            "price": 28
        }
    ]
}
```

> **Total Amount:** just like Purchase Orders, `total_amount` for a Sales Order is calculated on the server from `quantity × price` across all items. It is never taken directly from client input.

### Update Pending Order

```http
PUT /api/sales-orders/{salesOrder}
```

```json
{
    "customer_name": "Rahul Patil Updated",
    "items": [
        {
            "product_id": 1,
            "quantity": 3,
            "price": 28
        }
    ]
}
```

Only a sales order that is still `pending` can be updated. Once an order is `completed`, it becomes locked and cannot be edited.

### Complete

```http
POST /api/sales-orders/{salesOrder}/complete
```

On completion:
1. Available stock is checked against the requested quantity, for every item.
2. Product rows are locked during the stock check and update, to avoid problems from concurrent requests.
3. Stock decreases by the sold quantity, for each item.
4. An `OUT` stock history record is created for each item.
5. Order status changes from `pending` to `completed`.

If stock is insufficient for even one item, the entire completion request fails and stock remains unchanged — there is no partial stock movement.

## Order State Rules

This section explains the business rules behind purchase and sales order states, in plain terms.

### Purchase Orders

- A new purchase order is always created with status `pending`.
- Only a `pending` purchase order can be completed.
- Completing a purchase order increases stock for every item on the order.
  - Example: current stock of Product A is 20, and the purchase order has Product A × 10. After completion, stock becomes 30.
- A purchase order that is already `completed` cannot be completed again.

### Sales Orders

- A new sales order is always created with status `pending`.
- A `pending` sales order can be updated (customer name, items, quantities, prices).
- Completing a sales order decreases stock for every item on the order.
  - Example: current stock of Product A is 20, and the sales order has Product A × 5. After completion, stock becomes 15.
- A `completed` sales order cannot be updated.
- A `completed` sales order cannot be completed again.
- If the requested quantity is more than the available stock, completion is rejected and stock is not touched.
  - Example: current stock is 5 and the order asks for 10 — the API rejects this rather than letting stock go negative.
- Stock is never allowed to go below zero, under any circumstance.

## Additional API Endpoints

Beyond the operations explicitly listed in the assignment brief, this implementation includes a few practical REST endpoints that make the API easier and more complete to use in practice:

- **`GET /api/suppliers/{supplier}`** — fetch a single supplier's details, matching the pattern already used for Categories and Products.
- **`GET /api/purchase-orders`** and **`GET /api/purchase-orders/{purchaseOrder}`** — list and view purchase orders, needed to actually check order status before/after completion.
- **`GET /api/sales-orders`** and **`GET /api/sales-orders/{salesOrder}`** — list and view sales orders, for the same reason.
- **`PUT /api/sales-orders/{salesOrder}`** — allows a pending sales order to be corrected (say, if the customer changes the quantity) before it is completed.

These endpoints do not change any of the required business rules — they only make it possible to view and manage the resources the assignment already asks for.

# Common HTTP Responses

| Status | Meaning |
|---|---|
| 200 | Successful request |
| 201 | Resource created |
| 401 | Authentication required/invalid |
| 403 | Authenticated but not allowed to perform this action |
| 404 | Resource not found |
| 409 | Business conflict — either an invalid order state (e.g. completing an already-completed order) or a delete blocked by dependent records |
| 422 | Validation or business-rule error |

Example forbidden response:

```json
{
    "message": "You do not have permission to perform this action."
}
```

Example not-found response:

```json
{
    "message": "Resource not found."
}
```

## Resource Deletion and Referential Integrity

Categories, Suppliers and Products that already have dependent records (for example, a category that has products under it, or a supplier that has purchase order history) cannot be deleted.

In this case, the API returns `409 Conflict`:

```json
{
    "message": "Resource cannot be deleted because it is associated with existing records."
}
```

This is intentional, and is a good thing rather than a limitation. It shows that:

- Foreign key relationships are respected.
- Deletion is safe — you cannot accidentally break historical stock, purchase or sales data by deleting a category or supplier that is still in use.
- Past inventory and order records stay intact and trustworthy.

# Validation

Laravel Form Requests are used for validation.

Examples include:

- Required fields
- Valid email format
- Unique email
- Unique SKU
- Existing category/supplier/product
- Numeric prices
- Positive quantities
- Non-negative stock values
- Valid filter and sorting values

# Data Integrity and Business Logic

## Stock

Stock is stored as an unsigned integer, so it can never be a negative number at the database level.

Stock-changing operations use database transactions and row locking to protect stock from invalid concurrent updates — for example, two sales completing for the same product at the same time.

## Purchase

Completing a purchase increases stock and creates an `IN` stock history record. A completed purchase cannot be completed again.

## Sales

Before completing a sale, the system checks the required quantity against available stock. If stock is insufficient:

- The order is not completed.
- Stock is not reduced.
- No partial stock movement is created.

## Audit History

Every stock movement records the product, the quantity moved, the type (`IN` or `OUT`), any remarks, and the user (`created_by`) who performed the operation — giving a complete, traceable history of every stock change.

# Query Optimization

The API uses:

- Eager loading for relationships, to avoid N+1 query problems
- Database-level filtering, instead of filtering in PHP after fetching everything
- Pagination on all listing endpoints
- Database indexes on frequently filtered/sorted columns
- Allowlisted sorting, so `sort_by` can only use approved column names
- `whereColumn()` for low-stock filtering, comparing `stock_quantity` against `minimum_stock` directly in the database

# Database Relationships

```text
Category
 └── hasMany Products

Supplier
 ├── hasMany Products
 └── hasMany Purchase Orders

Product
 ├── belongsTo Category
 ├── belongsTo Supplier
 ├── hasMany Stock Histories
 ├── hasMany Purchase Order Items
 └── hasMany Sales Order Items

Purchase Order
 ├── belongsTo Supplier
 └── hasMany Items

Sales Order
 └── hasMany Items
```

# Testing

Run:

```bash
php artisan test
```

Current verification:

```text
96 tests passed
431 assertions passed
```

The tests cover authentication, RBAC, product operations and filters, stock operations, purchase workflows, sales workflows, validation, insufficient stock handling, unauthorized access, and not-found cases.

# Postman

A ready-to-import collection is included:

```text
inventory_management_postman_collection.json
```

Folders:

```text
01. Authentication
02. Categories
03. Suppliers
04. Products
05. Stock
06. Purchase Orders
07. Sales Orders
```

## Postman Usage

1. Import `inventory_management_postman_collection.json` into Postman.
2. Set the collection variable `base_url` to your local server, for example:
   ```text
   base_url = http://127.0.0.1:8000
   ```
3. Run the **Login** request with the Admin demo credentials.
4. The collection automatically saves the returned Sanctum token into the `token` collection variable — there is no need to copy-paste the token manually.
5. All protected requests automatically send:
   ```text
   Authorization: Bearer {{token}}
   ```
6. "Create" requests also automatically save the newly created resource's ID into the matching collection variable, for example:
   - `category_id`
   - `supplier_id`
   - `product_id`
   - `purchase_order_id`
   - `sales_order_id`

In short: just run Login once, and then the rest of the collection can be run in order without any manual setup.

### Running the Collection in Bulk (Collection Runner)

The folders are ordered so that running them top to bottom works correctly, with one important exception: three requests in the **Authentication** folder change or remove the active token, and will break the rest of a bulk run if left in.

Before doing a bulk run:

1. Run **Login** on its own first (not as part of the bulk run), using the Admin demo credentials. This stores the token automatically.
2. In the Collection Runner, **deselect** these three requests from the bulk run:
   - `Register` — not required for the demo flow; the seeded Admin/Manager/Staff users already exist.
   - `Change Password` — this revokes the current Sanctum token, so anything run after it will start failing with `401`.
   - `Logout` — this also invalidates the token, so it should only ever be run last, on its own.
3. Bulk-run the remaining folders in order: **Categories → Suppliers → Products → Stock → Purchase Orders → Sales Orders**.
4. If you want to demonstrate Register, Change Password, or Logout as well, run each of them individually at the end — and run Login again afterward if you need a fresh token for further testing.

# Recommended Demo Flow

For a quick project demonstration:

```text
Login
  ↓
Create Category (optional — seeded categories already exist)
  ↓
Create Supplier (optional — seeded suppliers already exist)
  ↓
Create Product
  ↓
Add Stock
  ↓
Create Purchase Order
  ↓
Complete Purchase Order
  ↓
Verify Stock Increased
  ↓
Create Sales Order
  ↓
Complete Sales Order
  ↓
Verify Stock Decreased
  ↓
Try Sale With Insufficient Stock
  ↓
Verify Request Is Rejected
```

# Project Structure

```text
app/
├── Exceptions/
├── Http/
│   ├── Controllers/Api/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
├── Models/
└── Services/

database/
├── factories/
├── migrations/
└── seeders/

routes/
└── api.php

tests/
├── Feature/
└── Unit/
```

# Notes

- The API returns JSON responses and is intended for backend/client consumption.
- Authentication uses Laravel Sanctum.
- Stock operations are transaction-based.
- Product stock is managed separately from normal product details.
- SQLite is used for local development and the schema can be configured for MySQL.

## Author

**Dilip Waghmare**

Backend API developed as a Laravel assignment demonstrating REST API development, authentication, authorization, inventory business logic, validation, database relationships, transactions, query optimization, and automated testing.