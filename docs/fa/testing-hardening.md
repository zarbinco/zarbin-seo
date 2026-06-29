# تست‌ها و Hardening

[بازگشت به فهرست فارسی](README.md)

برای اجرای تست‌های package:

```bash
composer test
composer format:test
```

## Consumer App E2E

برای تست نصب پکیج داخل یک اپ واقعی Laravel:

```bash
php scripts/e2e-consumer-app.php
```

این اسکریپت یک پروژه موقت Laravel می‌سازد و پکیج را از مسیر local نصب می‌کند. چون به Composer و Packagist وابسته است، timeout شبکه یا Packagist الزاما نشانه مشکل package نیست.

مستندات انگلیسی:

- [E2E](../e2e.md)
- [Hardening](../hardening.md)

## لایه‌های تست

- Unit tests: کلاس‌های کوچک مثل DTOها، renderers، resolvers و helpers
- Feature tests: رفتار package در Laravel/Testbench
- Bulletproof tests: config خراب، feature غیرفعال، table گم‌شده، route نامعتبر، UI/database mismatch و edge caseهای rendering
- Consumer app E2E: نصب واقعی در اپ موقت Laravel

این لایه‌ها کمک می‌کنند قابلیت‌های اختیاری، حتی وقتی کامل فعال نشده‌اند، باعث crash نشوند.
