# Changelog

All notable changes to `zarbinco/zarbin-seo` will be documented in this file.

## 0.1.0 - Unreleased

- Initial package skeleton.
- Added service provider, facade, configuration, tests, documentation, and CI foundation.
- Added the core SEO data object, fluent manager, SEO contract, model trait, text helpers, and global `seo()` helper.
- Added model, holder, route, array, and default SEO source resolution.
- Added HTML SEO rendering for title, meta, canonical, robots, Open Graph, Twitter cards, and basic JSON-LD.
- Added Blade meta component.
- Added multilingual SEO resolution.
- Added hreflang alternate link rendering.
- Added missing translation strategies for hide, fallback, and noindex.
- Added optional LocalizableSeo contract.
- Added sitemap URL data object.
- Added route, model, holder, and multilingual sitemap generation.
- Added sitemap XML and sitemap index rendering.
- Added robots.txt generation.
- Added optional public sitemap and robots routes.
- Added optional Sitemapable contract.
- Added optional SEO database override migration.
- Added SeoMeta model and repository.
- Added database override resolver.
- Added HasSeoMeta trait.
- Added route and model override APIs.
- Added social override support for Open Graph and Twitter cards.
- Added optional route-based SEO UI.
- Added embeddable Blade SEO form component.
- Added publishable package views for UI/forms.
- Added UI authorization gate support.
- Added database readiness warnings for UI.
- Added CommerceData DTO.
- Added optional CommerceSeo contract and HasCommerceSeo trait.
- Added commerce data resolver for model/config-based product SEO.
- Added Product JSON-LD and Offer schema support.
- Added locale-aware currency support.
- Added fluent commerce/product manager methods.
- Added install command.
- Added doctor/readiness command.
- Added route/model SEO check command.
- Added sitemap preview/export command.
- Added robots.txt preview/export command.
