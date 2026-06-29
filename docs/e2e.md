# Consumer App E2E Smoke Test

The consumer app smoke test creates a temporary Laravel application, installs Zarbin SEO through a Composer path repository, and verifies real installation behavior outside Orchestra Testbench.

Run it from the package repository root:

```bash
php scripts/e2e-consumer-app.php
```

Keep the temporary application for debugging:

```bash
php scripts/e2e-consumer-app.php --keep
```

Choose a Laravel skeleton constraint:

```bash
php scripts/e2e-consumer-app.php --laravel=^12.0
```

The script verifies:

- Composer path-repository installation.
- Laravel package discovery.
- Publish tags for config, migrations, and views.
- Published files under `config`, `database/migrations`, and `resources/views/vendor`.
- Package Artisan commands.
- Sitemap and robots routes.
- A real Blade view that calls `seo()->render()`.
- SQLite migrations inside the temporary app.

The script creates the app under the system temp directory and removes it by default. It does not commit or modify a real Laravel application.

