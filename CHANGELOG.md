# Changelog

All notable changes to `zarbinco/zarbin-seo` will be documented in this file.

## 0.1.1 - Unreleased

### Added

- Added consumer Laravel app smoke/E2E script.
- Added GitHub Actions E2E workflow for real package installation checks.
- Added E2E documentation.

## 0.1.0 - 2026-06-29

### Added

- Package foundation with service provider, facade, configuration, tests, documentation, CI, and Laravel package discovery.
- Core `SeoData` data object, fluent manager, SEO contract, model trait, text helpers, and global `seo()` helper.
- Model, holder, route, array, and default SEO source resolution.
- HTML SEO rendering for title, meta description, canonical, robots, Open Graph, Twitter/X cards, and basic JSON-LD.
- Blade meta component for layout usage.
- Multilingual SEO resolution with hreflang alternate links, `x-default`, and missing translation strategies for `hide`, `fallback`, and `noindex`.
- Optional `LocalizableSeo` contract.
- Sitemap URL data object, route/model/holder/multilingual sitemap generation, sitemap XML rendering, sitemap index rendering, robots.txt generation, and optional public sitemap/robots routes.
- Optional `Sitemapable` contract.
- Optional SEO database override migration, `SeoMeta` model, repository, database override resolver, `HasSeoMeta` trait, and route/model override APIs.
- Social override support for Open Graph and Twitter/X cards.
- Optional route-based SEO UI, embeddable Blade SEO form component, publishable package views, UI authorization gate support, and database readiness warnings.
- `CommerceData` DTO, optional `CommerceSeo` contract, `HasCommerceSeo` trait, commerce data resolver, Product JSON-LD, Offer schema, locale-aware currency support, and fluent commerce/product manager methods.
- Artisan developer-experience commands for install, doctor/readiness checks, route/model SEO inspection, sitemap preview/export, and robots.txt preview/export.
