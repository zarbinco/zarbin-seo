# SEO مدل‌محور

[بازگشت به فهرست فارسی](README.md)

در بیشتر پروژه‌های Laravel، داده‌های SEO از همان مدل‌های اصلی می‌آیند: `title`، `excerpt`، `content`، `slug`، `image` و موارد مشابه. Zarbin SEO تلاش می‌کند همین داده‌ها را استفاده کند و فقط وقتی لازم است، override دستی اضافه شود.

## قرارداد Seoable و trait HasSeo

برای کنترل دقیق‌تر خروجی SEO، مدل می‌تواند `Seoable` را implement کند و از `HasSeo` استفاده کند.

```php
use Illuminate\Database\Eloquent\Model;
use Zarbin\Seo\Concerns\HasSeo;
use Zarbin\Seo\Contracts\Seoable;

class Post extends Model implements Seoable
{
    use HasSeo;

    public function seoTitle(?string $locale = null): ?string
    {
        return $this->title;
    }

    public function seoDescription(?string $locale = null): ?string
    {
        return $this->excerpt;
    }

    public function seoCanonicalUrl(?string $locale = null): ?string
    {
        return route('posts.show', $this->slug);
    }

    public function seoImage(?string $locale = null): ?string
    {
        return $this->cover_image_url;
    }

    public function seoType(?string $locale = null): ?string
    {
        return 'Article';
    }
}
```

## mapping از config

اگر نمی‌خواهید یا نمی‌توانید مدل را تغییر دهید، mapping را در config تعریف کنید.

```php
'models' => [
    App\Models\Post::class => [
        'title' => 'title',
        'description' => ['excerpt', 'content'],
        'image' => 'cover_image_url',
        'route' => 'posts.show',
        'route_key' => 'slug',
        'type' => 'Article',
    ],
],
```

## اولویت resolve

اولویت کلی به این شکل است:

1. defaults و attributeهای رایج مدل
2. mappingهای config
3. متدهای مدل یا `Seoable`
4. وضعیت چندزبانه و hreflang
5. database override اختیاری
6. commerce data اختیاری

این ترتیب کمک می‌کند مدل‌های شما منبع اصلی داده بمانند، اما تیم محتوا بتواند در صورت نیاز مقدارهای SEO را دستی override کند.
