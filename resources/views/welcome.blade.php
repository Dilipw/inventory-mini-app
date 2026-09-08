<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inventory Management System | Portalwiz Assessment</title>

    <meta
        name="description"
        content="Inventory Management System - REST API based backend application built with Laravel for the Portalwiz Technologies Backend Developer Assessment."
    >

    <style>
        :root {
            --bg: #f7f8fc;
            --surface: #ffffff;
            --surface-soft: #f1f4f9;
            --text: #111827;
            --muted: #667085;
            --border: #e5e7eb;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --dark: #0f172a;
            --success: #15803d;
            --shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
            --radius: 18px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family:
                Inter,
                ui-sans-serif,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Helvetica,
                Arial,
                sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .container {
            width: min(1120px, calc(100% - 40px));
            margin: 0 auto;
        }

        /* Header */

        header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(229, 231, 235, 0.85);
        }

        .navbar {
            min-height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
        }

        .brand-mark {
            width: 40px;
            height: 40px;
            border-radius: 11px;
            display: grid;
            place-items: center;
            background: var(--dark);
            color: #ffffff;
            font-size: 15px;
            letter-spacing: -0.03em;
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .brand-title {
            font-size: 14px;
        }

        .brand-subtitle {
            color: var(--muted);
            font-size: 11px;
            font-weight: 500;
            margin-top: 3px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 26px;
            color: #475467;
            font-size: 14px;
            font-weight: 500;
        }

        .nav-links a {
            transition: color 0.2s ease;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-badge {
            padding: 7px 12px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--surface);
            color: var(--dark);
            font-size: 12px;
            font-weight: 600;
        }

        /* Hero */

        .hero {
            padding: 92px 0 80px;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: "";
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: rgba(37, 99, 235, 0.07);
            filter: blur(10px);
            top: -260px;
            right: -180px;
            pointer-events: none;
        }

        .hero-content {
            max-width: 850px;
            position: relative;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: #eff6ff;
            color: var(--primary-dark);
            border: 1px solid #dbeafe;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.02em;
            margin-bottom: 24px;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #22c55e;
        }

        h1 {
            font-size: clamp(42px, 7vw, 72px);
            line-height: 1.02;
            letter-spacing: -0.055em;
            max-width: 850px;
            margin-bottom: 24px;
        }

        .hero-description {
            max-width: 720px;
            color: var(--muted);
            font-size: 18px;
            line-height: 1.75;
            margin-bottom: 32px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            transition:
                transform 0.2s ease,
                background 0.2s ease,
                border-color 0.2s ease;
        }

        .button:hover {
            transform: translateY(-1px);
        }

        .button-primary {
            background: var(--dark);
            color: #ffffff;
        }

        .button-primary:hover {
            background: #1e293b;
        }

        .button-secondary {
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text);
        }

        .button-secondary:hover {
            border-color: #cbd5e1;
        }

        /* Stats */

        .stats {
            margin-top: 55px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1px;
            background: var(--border);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .stat {
            background: var(--surface);
            padding: 25px;
        }

        .stat-number {
            display: block;
            font-size: 25px;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--dark);
        }

        .stat-label {
            display: block;
            color: var(--muted);
            font-size: 13px;
            margin-top: 4px;
        }

        /* General sections */

        section {
            padding: 90px 0;
        }

        .section-header {
            max-width: 700px;
            margin-bottom: 42px;
        }

        .section-label {
            display: block;
            color: var(--primary);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .section-title {
            font-size: clamp(30px, 4vw, 44px);
            line-height: 1.15;
            letter-spacing: -0.04em;
            margin-bottom: 14px;
        }

        .section-description {
            color: var(--muted);
            font-size: 16px;
        }

        /* Feature cards */

        .features-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .feature-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 25px;
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        .feature-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }

        .feature-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 11px;
            background: var(--surface-soft);
            color: var(--dark);
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 16px;
            margin-bottom: 8px;
        }

        .feature-card p {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.65;
        }

        /* Architecture */

        .architecture {
            background: var(--dark);
            color: #ffffff;
        }

        .architecture .section-description {
            color: #94a3b8;
        }

        .architecture-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .architecture-card {
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.04);
            border-radius: var(--radius);
            padding: 28px;
        }

        .architecture-card h3 {
            font-size: 17px;
            margin-bottom: 18px;
        }

        .architecture-list {
            list-style: none;
            display: grid;
            gap: 11px;
        }

        .architecture-list li {
            color: #cbd5e1;
            font-size: 14px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .architecture-list li::before {
            content: "✓";
            color: #60a5fa;
            font-weight: 800;
        }

        /* Roles */

        .roles-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .role-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px;
        }

        .role-name {
            font-size: 19px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .role-description {
            color: var(--muted);
            font-size: 13px;
            margin-bottom: 20px;
        }

        .permission-list {
            list-style: none;
            display: grid;
            gap: 9px;
        }

        .permission-list li {
            font-size: 13px;
            display: flex;
            align-items: flex-start;
            gap: 9px;
        }

        .permission-list li::before {
            content: "✓";
            color: var(--success);
            font-weight: 800;
        }

        /* API */

        .api-section {
            background: var(--surface-soft);
        }

        .api-layout {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 22px;
        }

        .api-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px;
        }

        .api-card h3 {
            margin-bottom: 16px;
            font-size: 18px;
        }

        .endpoint-list {
            display: grid;
            gap: 10px;
        }

        .endpoint {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 13px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: #fafafa;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 12px;
        }

        .method {
            min-width: 45px;
            text-align: center;
            font-size: 10px;
            font-weight: 800;
            padding: 4px 6px;
            border-radius: 5px;
            background: #e0f2fe;
            color: #0369a1;
        }

        .endpoint-path {
            overflow-wrap: anywhere;
        }

        .stack {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
        }

        .stack-item {
            padding: 8px 11px;
            background: var(--surface-soft);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #344054;
        }

        /* Testing */

        .testing-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 32px;
            box-shadow: var(--shadow);
        }

        .testing-content h3 {
            font-size: 23px;
            margin-bottom: 7px;
        }

        .testing-content p {
            color: var(--muted);
            font-size: 14px;
        }

        .test-result {
            flex-shrink: 0;
            text-align: center;
            padding: 17px 25px;
            border-radius: 12px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
        }

        .test-number {
            display: block;
            color: var(--success);
            font-size: 27px;
            font-weight: 800;
        }

        .test-label {
            color: #166534;
            font-size: 11px;
            font-weight: 700;
        }

        /* Footer */

        footer {
            padding: 35px 0;
            background: var(--dark);
            color: #94a3b8;
        }

        .footer-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .footer-title {
            color: #ffffff;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 3px;
        }

        .footer-text {
            font-size: 12px;
        }

        .footer-right {
            font-size: 12px;
            text-align: right;
        }

        /* Responsive */

        @media (max-width: 900px) {
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .architecture-grid,
            .api-layout {
                grid-template-columns: 1fr;
            }

            .roles-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 700px) {
            .container {
                width: min(100% - 28px, 1120px);
            }

            .navbar {
                min-height: 64px;
            }

            .nav-links {
                display: none;
            }

            .hero {
                padding: 65px 0 55px;
            }

            h1 {
                font-size: clamp(38px, 12vw, 55px);
            }

            .hero-description {
                font-size: 16px;
            }

            section {
                padding: 65px 0;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .stats {
                grid-template-columns: 1fr 1fr;
            }

            .stat {
                padding: 20px;
            }

            .testing-box {
                flex-direction: column;
                align-items: flex-start;
            }

            .test-result {
                width: 100%;
            }

            .footer-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .footer-right {
                text-align: left;
            }
        }

        @media (max-width: 420px) {
            .stats {
                grid-template-columns: 1fr;
            }

            .hero-actions {
                flex-direction: column;
            }

            .button {
                width: 100%;
            }

            .brand-subtitle {
                display: none;
            }
        }
    </style>
</head>

<body>

<header>
    <div class="container navbar">

        <a href="/" class="brand">
            <span class="brand-mark">IM</span>

            <span class="brand-text">
                <span class="brand-title">Inventory Management System</span>
                <span class="brand-subtitle">Backend Developer Assessment</span>
            </span>
        </a>

        <nav class="nav-links">
            <a href="#features">Features</a>
            <a href="#architecture">Architecture</a>
            <a href="#roles">Roles</a>
            <a href="#api">API</a>
            <span class="nav-badge">Laravel REST API</span>
        </nav>

    </div>
</header>


<main>

    <!-- Hero -->

    <section class="hero">
        <div class="container">

            <div class="hero-content">

                <div class="eyebrow">
                    <span class="status-dot"></span>
                    Portalwiz Technologies · Backend Developer Assessment
                </div>

                <h1>
                    Inventory Management System
                </h1>

                <p class="hero-description">
                    A REST API based inventory management system built with Laravel,
                    designed to demonstrate clean architecture, authentication,
                    authorization, inventory operations, order management,
                    validation, and real-world business logic.
                </p>

                <div class="hero-actions">
                    <a href="#api" class="button button-primary">
                        Explore API
                    </a>

                    <a href="#architecture" class="button button-secondary">
                        View Architecture
                    </a>
                </div>

            </div>


            <div class="stats">

                <div class="stat">
                    <span class="stat-number">7</span>
                    <span class="stat-label">Core Modules</span>
                </div>

                <div class="stat">
                    <span class="stat-number">3</span>
                    <span class="stat-label">User Roles</span>
                </div>

                <div class="stat">
                    <span class="stat-number">REST</span>
                    <span class="stat-label">API Architecture</span>
                </div>

                <div class="stat">
                    <span class="stat-number">96</span>
                    <span class="stat-label">Automated Tests</span>
                </div>

            </div>

        </div>
    </section>


    <!-- Features -->

    <section id="features">
        <div class="container">

            <div class="section-header">
                <span class="section-label">Core Modules</span>

                <h2 class="section-title">
                    Everything needed to manage inventory operations.
                </h2>

                <p class="section-description">
                    The system covers the complete inventory lifecycle from
                    authentication and product management to purchasing,
                    sales, and stock tracking.
                </p>
            </div>


            <div class="features-grid">

                <article class="feature-card">
                    <div class="feature-icon">01</div>

                    <h3>Authentication</h3>

                    <p>
                        Laravel Sanctum based registration, login, logout,
                        profile and password management with role-based access.
                    </p>
                </article>


                <article class="feature-card">
                    <div class="feature-icon">02</div>

                    <h3>Products</h3>

                    <p>
                        Product CRUD operations with unique SKU,
                        category and supplier relationships, pricing,
                        stock thresholds and status.
                    </p>
                </article>


                <article class="feature-card">
                    <div class="feature-icon">03</div>

                    <h3>Stock Management</h3>

                    <p>
                        Controlled stock addition and reduction with
                        transactional updates and complete stock movement history.
                    </p>
                </article>


                <article class="feature-card">
                    <div class="feature-icon">04</div>

                    <h3>Purchase Orders</h3>

                    <p>
                        Supplier purchase orders with line items,
                        calculated totals and automatic stock increase
                        when an order is completed.
                    </p>
                </article>


                <article class="feature-card">
                    <div class="feature-icon">05</div>

                    <h3>Sales Orders</h3>

                    <p>
                        Customer sales orders with stock availability
                        checks and automatic stock reduction on completion.
                    </p>
                </article>


                <article class="feature-card">
                    <div class="feature-icon">06</div>

                    <h3>Categories & Suppliers</h3>

                    <p>
                        Structured master data management with relationships
                        connecting products, suppliers and purchase orders.
                    </p>
                </article>


                <article class="feature-card">
                    <div class="feature-icon">07</div>

                    <h3>Search & Filtering</h3>

                    <p>
                        Product search, category and supplier filtering,
                        low-stock detection, sorting and pagination.
                    </p>
                </article>


                <article class="feature-card">
                    <div class="feature-icon">08</div>

                    <h3>Validation</h3>

                    <p>
                        Dedicated Form Requests enforce required fields,
                        unique values, valid relationships and positive quantities.
                    </p>
                </article>

            </div>

        </div>
    </section>


    <!-- Architecture -->

    <section id="architecture" class="architecture">
        <div class="container">

            <div class="section-header">
                <span class="section-label">Engineering</span>

                <h2 class="section-title">
                    Built with maintainability and data integrity in mind.
                </h2>

                <p class="section-description">
                    Business rules are separated from HTTP concerns,
                    while database operations are protected against
                    inconsistent inventory states.
                </p>
            </div>


            <div class="architecture-grid">

                <div class="architecture-card">

                    <h3>Application Architecture</h3>

                    <ul class="architecture-list">
                        <li>Thin API controllers</li>
                        <li>Service layer for business logic</li>
                        <li>Dedicated Form Request validation</li>
                        <li>API Resources for consistent responses</li>
                        <li>Eloquent relationships and eager loading</li>
                        <li>Role-based middleware authorization</li>
                    </ul>

                </div>


                <div class="architecture-card">

                    <h3>Data Integrity & Performance</h3>

                    <ul class="architecture-list">
                        <li>Database transactions for stock operations</li>
                        <li>Row locking for concurrent stock updates</li>
                        <li>Stock cannot become negative</li>
                        <li>Server-calculated order totals</li>
                        <li>Indexed searchable and relational fields</li>
                        <li>Allowlisted sorting and pagination</li>
                    </ul>

                </div>

            </div>

        </div>
    </section>


    <!-- Roles -->

    <section id="roles">
        <div class="container">

            <div class="section-header">
                <span class="section-label">Authorization</span>

                <h2 class="section-title">
                    Role-based access control.
                </h2>

                <p class="section-description">
                    Access is restricted according to operational responsibility,
                    keeping administrative and inventory activities separated.
                </p>
            </div>


            <div class="roles-grid">

                <article class="role-card">

                    <div class="role-name">Admin</div>

                    <p class="role-description">
                        Full system access and administrative control.
                    </p>

                    <ul class="permission-list">
                        <li>Manage categories</li>
                        <li>Manage suppliers</li>
                        <li>Create and update products</li>
                        <li>Delete products</li>
                        <li>Manage stock and orders</li>
                    </ul>

                </article>


                <article class="role-card">

                    <div class="role-name">Manager</div>

                    <p class="role-description">
                        Day-to-day inventory and order management.
                    </p>

                    <ul class="permission-list">
                        <li>View categories and suppliers</li>
                        <li>Create and update products</li>
                        <li>Add and reduce stock</li>
                        <li>Create and complete purchases</li>
                        <li>Create, update and complete sales</li>
                    </ul>

                </article>


                <article class="role-card">

                    <div class="role-name">Staff</div>

                    <p class="role-description">
                        Read-only access for inventory visibility.
                    </p>

                    <ul class="permission-list">
                        <li>View categories</li>
                        <li>View suppliers</li>
                        <li>View products</li>
                        <li>View stock history</li>
                        <li>View purchase and sales orders</li>
                    </ul>

                </article>

            </div>

        </div>
    </section>


    <!-- API -->

    <section id="api" class="api-section">
        <div class="container">

            <div class="section-header">
                <span class="section-label">REST API</span>

                <h2 class="section-title">
                    API-first inventory management.
                </h2>

                <p class="section-description">
                    Protected endpoints use Laravel Sanctum bearer authentication,
                    while resources follow consistent JSON API responses.
                </p>
            </div>


            <div class="api-layout">

                <div class="api-card">

                    <h3>Example Endpoints</h3>

                    <div class="endpoint-list">

                        <div class="endpoint">
                            <span class="method">POST</span>
                            <span class="endpoint-path">/api/login</span>
                        </div>

                        <div class="endpoint">
                            <span class="method">GET</span>
                            <span class="endpoint-path">/api/products</span>
                        </div>

                        <div class="endpoint">
                            <span class="method">POST</span>
                            <span class="endpoint-path">/api/products</span>
                        </div>

                        <div class="endpoint">
                            <span class="method">POST</span>
                            <span class="endpoint-path">/api/stock/add</span>
                        </div>

                        <div class="endpoint">
                            <span class="method">POST</span>
                            <span class="endpoint-path">/api/purchase-orders</span>
                        </div>

                        <div class="endpoint">
                            <span class="method">POST</span>
                            <span class="endpoint-path">/api/sales-orders</span>
                        </div>

                    </div>

                </div>


                <div class="api-card">

                    <h3>Technology Stack</h3>

                    <div class="stack">

                        <span class="stack-item">Laravel 13</span>
                        <span class="stack-item">PHP 8.3</span>
                        <span class="stack-item">REST API</span>
                        <span class="stack-item">Laravel Sanctum</span>
                        <span class="stack-item">Eloquent ORM</span>
                        <span class="stack-item">SQLite</span>
                        <span class="stack-item">MySQL Ready</span>
                        <span class="stack-item">PHPUnit</span>
                        <span class="stack-item">Postman</span>

                    </div>

                </div>

            </div>

        </div>
    </section>


    <!-- Testing -->

    <section>
        <div class="container">

            <div class="testing-box">

                <div class="testing-content">

                    <h3>Tested and verified.</h3>

                    <p>
                        The application includes automated tests covering
                        authentication, authorization, CRUD operations,
                        stock management, purchase orders, sales orders,
                        validation and business rules.
                    </p>

                </div>


                <div class="test-result">

                    <span class="test-number">96</span>

                    <span class="test-label">
                        TESTS PASSED
                    </span>

                </div>

            </div>

        </div>
    </section>

</main>


<footer>

    <div class="container footer-content">

        <div>
            <div class="footer-title">
                Inventory Management System
            </div>

            <div class="footer-text">
                Backend Developer Assessment · Portalwiz Technologies Pvt. Ltd.
            </div>
        </div>


        <div class="footer-right">
            Built with Laravel · REST API · PHP
        </div>

    </div>

</footer>

</body>
</html>