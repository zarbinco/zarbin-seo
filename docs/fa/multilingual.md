# SEO چندزبانه

[بازگشت به فهرست فارسی](README.md)

Zarbin SEO بدون وابستگی به پکیج ترجمه خاص، می‌تواند locale-aware resolve کند و برای صفحه‌های چندزبانه hreflang بسازد.

## config نمونه

```php
'localization' => [
    'enabled' => true,
    'locales' => ['fa', 'en'],
    'default_locale' => 'fa',
    'route_parameter' => 'locale',
    'missing_translation_strategy' => 'hide',
    'generate_hreflang' => true,
    'x_default' => 'fa',
],
```

## قرارداد LocalizableSeo

```php
use Illuminate\Database\Eloquent\Model;
use Zarbin\Seo\Concerns\HasSeo;
use Zarbin\Seo\Contracts\LocalizableSeo;
use Zarbin\Seo\Contracts\Seoable;

class Post extends Model implements Seoable, LocalizableSeo
{
    use HasSeo;

    public function seoLocales(): array
    {
        return ['fa', 'en'];
    }

    public function hasSeoLocale(string $locale): bool
    {
        return filled($this->getTranslation('title', $locale));
    }

    public function seoUrlForLocale(string $locale): ?string
    {
        return route('posts.show', ['locale' => $locale, 'post' => $this->slug]);
    }
}
```

## استراتژی نبود ترجمه

- `hide`: locale ناموجود در hreflang نمایش داده نمی‌شود.
- `fallback`: URL زبان پیش‌فرض می‌تواند جایگزین شود. با احتیاط استفاده کنید تا صفحه با زبان اشتباه index نشود.
- `noindex`: صفحه می‌تواند render شود، اما robots به `noindex` تغییر می‌کند.

اگر یک صفحه عنوان یا محتوای انگلیسی ندارد، معمولا `hide` انتخاب امن‌تری است. `x-default` هم می‌تواند به locale پیش‌فرض یا یک URL مشخص اشاره کند.
