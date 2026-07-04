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

---

## 🧩 Architecture Overview

The application follows a tenant-aware request flow:

1. A request is sent with a tenant identifier
2. The tenant is identified by custom middleware
3. The database connection is switched dynamically
4. Sanctum authenticates the user using the tenant database
5. Controllers and models operate on the active tenant data only

---

## 🗂 Project Structure

This project uses a domain-oriented structure for models and a standard Laravel structure for HTTP handling.

Example structure:
```text
app/
├── Domain/
│   ├── Task/
│   │   └── Models/
│   │       ├── Project.php
│   │       └── Task.php
│   └── Tenant/
│       └── Models/
│           ├── Tenant.php
│           └── PersonalAccessToken.php
├── Http/
│   ├── Controllers/
│   └── Middleware/
│       └── IdentifyTenant.php
└── Providers/
└── AppServiceProvider.php
