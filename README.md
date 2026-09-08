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

| Role | Access |
|---|---|
| Admin | Full access |
| Manager | Manage inventory and orders |
| Staff | Read-only access |

Authorization is handled using custom role middleware.

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

## 2. Categories

| Method | Endpoint | Access |
|---|---|---|
| GET | `/api/categories` | All authenticated roles |
| GET | `/api/categories/{category}` | All authenticated roles |
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
| GET | `/api/suppliers` | All authenticated roles |
| GET | `/api/suppliers/{supplier}` | All authenticated roles |
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
| GET | `/api/products` | All authenticated roles |
| GET | `/api/products/{product}` | All authenticated roles |
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

The API uses an allowlist for sorting fields.

> Product stock is not changed through normal product update. Stock is managed through stock operations and purchase/sales completion.

## 5. Stock

| Method | Endpoint | Access |
|---|---|---|
| POST | `/api/stock/add` | Admin / Manager |
| POST | `/api/stock/reduce` | Admin / Manager |
| GET | `/api/stock/history` | All authenticated roles |

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

## 6. Purchase Orders

| Method | Endpoint | Access |
|---|---|---|
| POST | `/api/purchase-orders` | Admin / Manager |
| GET | `/api/purchase-orders` | All authenticated roles |
| GET | `/api/purchase-orders/{purchaseOrder}` | All authenticated roles |
| POST | `/api/purchase-orders/{purchaseOrder}/complete` | Admin / Manager |

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

Total amount is calculated from `quantity × price`.

### Complete

```http
POST /api/purchase-orders/{purchaseOrder}/complete
```

On completion:
1. Product stock increases.
2. `IN` stock history is created.
3. Order status becomes `completed`.
4. The same order cannot be completed again.

## 7. Sales Orders

| Method | Endpoint | Access |
|---|---|---|
| POST | `/api/sales-orders` | Admin / Manager |
| GET | `/api/sales-orders` | All authenticated roles |
| GET | `/api/sales-orders/{salesOrder}` | All authenticated roles |
| PUT | `/api/sales-orders/{salesOrder}` | Admin / Manager |
| POST | `/api/sales-orders/{salesOrder}/complete` | Admin / Manager |

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

Only pending sales orders can be updated.

### Complete

```http
POST /api/sales-orders/{salesOrder}/complete
```

On completion:
1. Required stock is checked.
2. Product rows are locked during the stock operation.
3. Stock decreases.
4. `OUT` stock history is created.
5. Order status becomes `completed`.

If stock is insufficient, the transaction fails and stock remains unchanged.

# Common HTTP Responses

| Status | Meaning |
|---|---|
| 200 | Successful request |
| 201 | Resource created |
| 401 | Authentication required/invalid |
| 403 | Authenticated but not allowed |
| 404 | Resource not found |
| 409 | Invalid order state/business conflict |
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

Stock is stored as an unsigned integer.

Stock-changing operations use database transactions and row locking to protect stock from invalid concurrent updates.

## Purchase

Completing a purchase increases stock and creates an `IN` stock history record.

A completed purchase cannot be completed again.

## Sales

Before completing a sale, the system checks the required quantity against available stock.

If stock is insufficient:

- The order is not completed.
- Stock is not reduced.
- No partial stock movement is created.

## Audit History

Stock movements record:

- Product
- Quantity
- Type (`IN` / `OUT`)
- Remarks
- User who performed the operation

# Query Optimization

The API uses:

- Eager loading for relationships
- Database-level filtering
- Pagination
- Database indexes
- Allowlisted sorting
- `whereColumn()` for low-stock filtering

This helps reduce unnecessary queries and avoids common N+1 query problems.

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

Set the collection variable:

```text
base_url = http://127.0.0.1:8000
```

After login, set:

```text
token = <sanctum-token>
```

Protected requests use:

```text
Authorization: Bearer {{token}}
```

# Recommended Demo Flow

For a quick project demonstration:

```text
Login
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
