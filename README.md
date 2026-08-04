# Laravel Multi-Tenant Project Manager API

A multi-tenant Project Management API built with **Laravel 10**, **MySQL**, and **Laravel Sanctum** using a clean and scalable structure.

This project uses a **database-per-tenant** architecture, meaning each tenant has its own isolated database. Authentication, project management, and task management are all handled inside the active tenant database.

---

## 🚀 Features

- **Multi-Tenancy (Database per Tenant):** each tenant has its own separate database
- **Tenant Identification Middleware:** automatically detects the current tenant and switches the database connection
- **Tenant-Aware Authentication:** Laravel Sanctum is configured to use the tenant database
- **Project Management API:** create and list projects per tenant
- **Task Management API:** create and list tasks per tenant
- **Domain-Oriented Structure:** project models are organized in a clean architecture
- **Data Isolation:** tenants cannot access each other’s records
- **Async Tenant Reporting:** generate tenant reports in the background using Laravel queues
- **Custom Tenant Migration Command:** run tenant database migrations using the custom `php artisan tenant:migrate` command

---

## 🧩 Architecture Overview

The application follows a tenant-aware request flow:

1. A request is sent with a tenant identifier
2. The tenant is identified by custom middleware
3. The database connection is switched dynamically
4. Sanctum authenticates the user using the tenant database
5. Controllers and models operate on the active tenant data only

---

## 🛠 Usage

### Tenant Migration

Run tenant migrations for all tenant databases:
php artisan tenant:migrate

Run tenant migrations for a specific tenant database:
php artisan tenant:migrate --tenant=alpha_taskino

### Async Tenant Reports

Tenant report generation uses Laravel queues. To process queued report jobs, run:
php artisan queue:work

The report flow is:
1. A tenant user requests report generation
2. The application creates a pending tenant report
3. A queued job processes the report in the background
4. The user can check the report status
5. When completed, the user can download the generated report

## 🗂 Project Structure

This project uses a domain-oriented structure for models and a standard Laravel structure for HTTP handling.

Example structure:
app/
├── Console/
│   └── Commands/
│       └── TenantMigrateCommand.php
├── Domain/
│   ├── Task/
│   │   └── Models/
│   │       ├── Project.php
│   │       └── Task.php
│   └── Tenant/
│       ├── Jobs/
│       │   └── ProcessTenantReport.php
│       └── Models/
│           ├── Tenant.php
│           ├── TenantReport.php
│           └── PersonalAccessToken.php
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── V1/
│   │           └── TenantReportController.php
│   └── Middleware/
│       └── IdentifyTenant.php
└── Providers/
└── AppServiceProvider.php
