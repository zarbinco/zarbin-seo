<?php

declare(strict_types=1);

return [
    'navigation' => [
        'dashboard' => 'داشبورد',
        'routes' => 'مسیرها',
        'models' => 'مدل‌ها',
        'back_to_routes' => 'بازگشت به مسیرها',
        'back_to_models' => 'بازگشت به مدل‌ها',
        'aria' => 'ناوبری SEO',
    ],

    'layout' => [
        'standalone_title' => 'Zarbin SEO',
        'host_missing' => 'layout میزبان در دسترس نیست؛ layout داخلی پکیج استفاده می‌شود.',
    ],

    'direction' => [
        'rtl' => 'راست به چپ',
        'ltr' => 'چپ به راست',
    ],

    'components' => [
        'panel_title' => 'پنل SEO',
        'routes_title' => 'مسیرهای SEO',
        'models_title' => 'مدل‌های SEO',
        'custom_action_missing' => 'برای ذخیره از مسیر ادمین خودتان، action سفارشی را ارسال کنید.',
        'source_not_found' => 'منبع SEO درخواستی پیدا نشد.',
        'hosted_routes_disabled' => 'مسیرهای hosted UI سئو غیرفعال هستند یا در دسترس نیستند.',
    ],

    'dashboard' => [
        'title' => 'Zarbin SEO',
        'description' => 'مدیریت وضعیت SEO و overrideهای مسیرها.',
        'status' => 'وضعیت',
        'database_ready' => 'دیتابیس آماده است',
        'database_not_ready' => 'دیتابیس آماده نیست',
        'routes_total' => 'مسیرها',
        'routes_complete' => 'کامل',
        'routes_incomplete' => 'ناقص',
        'models_total' => 'مدل‌ها',
        'models_complete' => 'کامل',
        'models_incomplete' => 'ناقص',
        'route_overrides' => 'Override مسیرها',
        'route_overrides_description' => 'ویرایش دستی SEO برای مسیرهای تعریف‌شده در config.',
        'manage_route_overrides' => 'مدیریت مسیرها',
        'model_overrides' => 'Override مدل‌ها',
        'model_overrides_description' => 'ویرایش دستی SEO برای مدل‌هایی که به‌صورت امن و صریح در inventory تعریف شده‌اند.',
        'manage_model_overrides' => 'مدیریت مدل‌ها',
        'yes' => 'بله',
        'no' => 'خیر',
        'status_items' => [
            'ui_enabled' => 'UI فعال است',
            'database_overrides_enabled' => 'overrideهای دیتابیس فعال است',
            'table_exists' => 'جدول SEO وجود دارد',
            'sitemap_enabled' => 'Sitemap فعال است',
            'robots_enabled' => 'robots.txt فعال است',
            'localization_enabled' => 'چندزبانه فعال است',
        ],
    ],

    'routes' => [
        'title' => 'مسیرهای SEO',
        'edit_title' => 'ویرایش SEO مسیر',
        'description' => 'صفحه‌های route-only تعریف‌شده برای SEO.',
        'empty' => 'هیچ مسیر SEO تعریف نشده است.',
        'key' => 'کلید',
        'label' => 'عنوان',
        'locale' => 'زبان',
        'status' => 'وضعیت',
        'missing' => 'کمبودها',
        'warnings' => 'هشدارها',
        'actions' => 'عملیات',
        'edit' => 'ویرایش',
        'edit_unavailable' => 'لینک ویرایش در دسترس نیست',
        'complete' => 'کامل',
        'incomplete' => 'ناقص',
    ],

    'models' => [
        'title' => 'مدل‌های SEO',
        'edit_title' => 'ویرایش SEO مدل',
        'description' => 'Inventory مدل‌ها و holderهایی که صریحا برای SEO تعریف شده‌اند.',
        'empty' => 'هیچ آیتم مدل برای SEO تعریف نشده است.',
        'disabled' => 'Inventory مدل‌ها غیرفعال است. برای نمایش مدل‌ها باید ui.inventory.models.enabled را فعال کنید و برای هر مدل source صریح تعریف کنید.',
        'class' => 'مدل',
        'item' => 'آیتم',
        'key' => 'کلید',
        'edit' => 'ویرایش',
        'not_found' => 'آیتم SEO مدل پیدا نشد.',
    ],

    'form' => [
        'legend' => 'Override SEO',
        'save' => 'ذخیره SEO',
        'save_override' => 'ذخیره override',
        'delete' => 'حذف override',
        'reset' => 'بازنشانی',
        'database_warning' => 'overrideهای دیتابیس آماده نیستند. ذخیره غیرفعال است.',
        'database_preview_warning' => 'overrideهای دیتابیس آماده نیستند. فرم فقط برای پیش‌نمایش نمایش داده شده و ذخیره غیرفعال است.',
        'database_setup_warning' => 'overrideهای دیتابیس SEO آماده نیستند. migration را publish و اجرا کنید و سپس database overrides را فعال کنید.',
        'saved' => 'override SEO ذخیره شد.',
        'deleted' => 'override SEO حذف شد.',
        'not_saved' => 'override SEO ذخیره نشد.',
        'not_deleted' => 'override SEO حذف نشد.',
        'model_saved' => 'override SEO مدل ذخیره شد.',
        'model_deleted' => 'override SEO مدل حذف شد.',
        'model_not_saved' => 'override SEO مدل ذخیره نشد.',
        'model_not_deleted' => 'override SEO مدل حذف نشد.',
        'validation_errors' => 'برخی فیلدها خطا دارند.',
    ],

    'fields' => [
        'title' => [
            'label' => 'عنوان SEO',
            'hint' => 'برای عنوان صفحه و عنوان نتیجه جستجو استفاده می‌شود.',
        ],
        'description' => [
            'label' => 'توضیحات متا',
            'hint' => 'توضیح کوتاه برای نتیجه‌های جستجو.',
        ],
        'canonical' => [
            'label' => 'آدرس Canonical',
            'hint' => 'آدرس اصلی و ترجیحی این صفحه.',
        ],
        'robots' => [
            'label' => 'وضعیت Robots',
            'hint' => 'مشخص می‌کند موتورهای جستجو صفحه و لینک‌ها را چطور بررسی کنند.',
        ],
        'image' => [
            'label' => 'آدرس تصویر',
            'hint' => 'برای پیش‌نمایش اشتراک‌گذاری در شبکه‌های اجتماعی.',
        ],
        'og_title' => [
            'label' => 'عنوان Open Graph',
            'hint' => 'عنوان اختیاری برای اشتراک‌گذاری اجتماعی.',
        ],
        'og_description' => [
            'label' => 'توضیحات Open Graph',
            'hint' => 'توضیح اختیاری برای اشتراک‌گذاری اجتماعی.',
        ],
        'og_image' => [
            'label' => 'تصویر Open Graph',
            'hint' => 'تصویر اختیاری برای اشتراک‌گذاری اجتماعی.',
        ],
        'twitter_title' => [
            'label' => 'عنوان Twitter/X',
            'hint' => 'عنوان اختیاری برای کارت Twitter/X.',
        ],
        'twitter_description' => [
            'label' => 'توضیحات Twitter/X',
            'hint' => 'توضیح اختیاری برای کارت Twitter/X.',
        ],
        'twitter_image' => [
            'label' => 'تصویر Twitter/X',
            'hint' => 'تصویر اختیاری برای کارت Twitter/X.',
        ],
        'schema_type' => [
            'label' => 'نوع Schema',
            'hint' => 'مثلا: WebPage، CollectionPage، Product یا Article.',
        ],
        'extra' => [
            'label' => 'JSON اضافی',
            'hint' => 'متادیتای پیشرفته به صورت JSON.',
        ],
    ],

    'preview' => [
        'title' => 'پیش‌نمایش',
        'search_result' => 'پیش‌نمایش نتیجه جستجو',
        'raw_html' => 'پیش‌نمایش HTML خام',
        'no_title' => 'صفحه بدون عنوان',
        'no_description' => 'توضیحات متا موجود نیست.',
        'no_url' => 'آدرس Canonical موجود نیست.',
        'approximation' => 'این فقط یک شبیه‌سازی ظاهری است و نمایش واقعی موتورهای جستجو ممکن است متفاوت باشد.',
        'warnings' => [
            'missing_title' => 'عنوان SEO وارد نشده است.',
            'missing_url' => 'آدرس Canonical وارد نشده است.',
            'missing_description' => 'توضیحات متا وارد نشده است.',
            'long_title' => 'عنوان SEO ممکن است طولانی باشد.',
            'long_description' => 'توضیحات متا ممکن است طولانی باشد.',
        ],
    ],

    'status' => [
        'complete_symbol' => '✓',
        'incomplete_symbol' => '×',
    ],
];
