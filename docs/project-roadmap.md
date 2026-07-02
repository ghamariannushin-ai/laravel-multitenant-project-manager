# Laravel Multi-Tenant Project Manager

## Project Name

`laravel-multitenant-project-manager`

## Goal

This project is a learning-focused Laravel SaaS-style application that demonstrates multi-database tenancy, clean architecture, service layer, repository pattern, custom Artisan commands, middleware-based tenant identification, and dynamic database switching.

The main goal is to improve Laravel skills and create a strong GitHub portfolio project.

---

## What is a Tenant?

A tenant is a separate customer, company, or organization using the same application.

Example:

- Alpha Company
- Beta Company
- ParsTech Company

All tenants use the same Laravel codebase, but each tenant has its own separate database.

Example domains:
```text
alpha.taskino.test
beta.taskino.test
pars.taskino.test
