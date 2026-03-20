# Architecture: dashproducts

## Purpose

A PrestaShop native module providing the "Best Products" dashboard widget — a table in the Back Office dashboard listing top-selling products, best-viewed products, and most-wishlisted products over a configurable time period.

## Directory Structure

```
dashproducts.php                 — Module entry point: hook registration, product data queries, widget rendering
views/
  templates/hook/                — Smarty template for the dashboard widget
translations/                    — Module translation strings
upgrade/
  upgrade-2.1.2.php              — Migration for version 2.1.2
tests/phpstan/                   — PHPStan static analysis configuration
```

## Key Design Decisions

- **Read-only dashboard widget** — queries PrestaShop's existing order, product, and stats tables; no custom database tables.
- **Configurable period** — the displayed time range is configurable from the dashboard widget settings.
- **Single-class module** — all logic in `dashproducts.php` per PrestaShop conventions.

## Extension Points

- Override the Smarty template to customise the displayed columns or layout.
