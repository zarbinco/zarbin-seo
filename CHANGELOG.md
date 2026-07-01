# Changelog

All notable changes to `zarbinco/zarbin-seo` will be documented in this file.

## 0.2.0 - Unreleased

### Added

- Added opt-in model and holder inventory for the optional Blade UI.
- Added model SEO edit screens backed by database overrides.
- Added safe model inventory source handling.
- Added English and Persian translation files for the optional Blade UI.
- Added publishable UI translations for custom languages.
- Added search result preview for SEO title, canonical URL, and meta description.
- Added SEO UI page inventory with completion status indicators.
- Added configurable SEO completion checks.
- Added robots dropdown support to the optional Blade UI.
- Added translation-aware and relation-aware commerce field resolution.
- Added optional Offer generation controls for Product schema.
- Added configurable sitemap XML response content type.
- Added configurable locale URL strategy for multilingual route generation.
- Added configurable per-locale sitemap paths.
- Added sitemap index entries for localized sitemap files.

### Changed

- UI inventory can now combine route-only and configured model SEO items.
- Replaced hard-coded UI labels, hints, buttons, warnings, and messages with translation keys.
- Product schema can now be generated without Offer for catalog/company product pages.
- Made sitemap hreflang alternates opt-in for cleaner default sitemap XML output.
- Added doctor/readiness notice for sitemap xhtml alternates.
- Hardened localized sitemap routes, content types, locale-scoped route entries, and sitemap base URL handling.
- Improved browser compatibility for valid sitemap XML responses by allowing text/xml content type.
- Improved localized URL generation safety for prefixed and default-locale-without-prefix URL structures.

### Fixed

- Fixed XML-safe rendering of sitemap hreflang xhtml alternate links.
- Ensured default and localized sitemap endpoints return proper XML HTTP responses with application/xml content type.

## 0.1.1 - 2026-06-29

### Added

- Added consumer Laravel app smoke/E2E script.
- Added GitHub Actions E2E workflow for real package installation checks.
- Added E2E documentation.
- Added bulletproof hardening tests for optional database, routes, localization, rendering, sitemap/robots, commerce, UI, and commands.
- Added hardening documentation.
- Added Persian documentation for installation, quick start, model/holder/route SEO, rendering, multilingual SEO, sitemap/robots, database overrides, UI, commerce schema, commands, configuration, and testing.

### Fixed

- Guarded malformed localized URL route config and throwing sitemap generators from crashing SEO resolution.

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
