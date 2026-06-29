# Hardening Tests

Zarbin SEO includes a bulletproof hardening layer alongside the regular unit, feature, and consumer-app smoke tests.

These tests exercise intentionally awkward conditions: disabled optional features, missing database tables, invalid route names, malformed localization configuration, UI/database mismatches, unsafe rendering values, sitemap source failures, and malformed commerce data.

## Safety Expectations

- Optional features are safe by default.
- Database overrides do not crash when the SEO meta table is missing and `database.ignore_missing_table` is `true`.
- The optional UI is disabled by default and should show friendly warnings when database overrides are not ready.
- Invalid routes, bad sitemap sources, malformed localization settings, and unexpected source values should not produce fatal errors.
- Rendering paths escape HTML, JSON-LD, and XML-sensitive values.

## Running Tests

```bash
composer test
```

The broader release smoke test can also be run when Composer and Packagist are reachable:

```bash
php scripts/e2e-consumer-app.php
```

The E2E script creates a temporary Laravel app and depends on Composer network availability. A Packagist timeout is an environment failure, not automatically a package failure.

## Test Layers

- Unit tests cover individual DTOs, support classes, renderers, resolvers, and generators.
- Feature tests cover package behavior through Laravel/Testbench.
- Bulletproof tests cover broken config and optional-resource failure modes.
- Consumer app E2E verifies real package installation in a temporary Laravel application.
