<?php

declare(strict_types=1);

namespace Zarbin\Seo\Models;

use Illuminate\Database\Eloquent\Model;
use Throwable;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Support\AttributeReader;

class SeoMeta extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'locale',
        'title',
        'description',
        'canonical_url',
        'robots',
        'image',
        'og_title',
        'og_description',
        'og_image',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'schema_type',
        'extra',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'robots' => 'array',
        'extra' => 'array',
    ];

    public function getTable(): string
    {
        return (string) config('zarbin-seo.database.table', 'seo_meta');
    }

    public static function normalizedLocale(?string $locale = null): string
    {
        return $locale === null ? '' : trim($locale);
    }

    public static function typeForSource(object $source): string
    {
        if (method_exists($source, 'getMorphClass')) {
            try {
                $type = $source->getMorphClass();

                if (is_string($type) && trim($type) !== '') {
                    return $type;
                }
            } catch (Throwable) {
                return $source::class;
            }
        }

        return $source::class;
    }

    public static function idForSource(object $source): ?string
    {
        if (method_exists($source, 'getKey')) {
            try {
                $key = $source->getKey();

                if ($key !== null && $key !== '') {
                    return (string) $key;
                }
            } catch (Throwable) {
                //
            }
        }

        $id = AttributeReader::get($source, 'id');

        if ($id === null || $id === '') {
            return null;
        }

        return is_scalar($id) ? (string) $id : null;
    }

    public static function routeType(): string
    {
        $type = config('zarbin-seo.database.route_type', 'route');

        return is_string($type) && trim($type) !== '' ? trim($type) : 'route';
    }

    public function toSeoData(): SeoData
    {
        $extra = is_array($this->extra) ? $this->extra : [];

        foreach ([
            'og_title',
            'og_description',
            'og_image',
            'twitter_title',
            'twitter_description',
            'twitter_image',
        ] as $key) {
            $value = $this->getAttribute($key);

            if ($this->filled($value)) {
                $extra[$key] = $value;
            }
        }

        return SeoData::make($this->nonEmpty([
            'title' => $this->title,
            'description' => $this->description,
            'canonical' => $this->canonical_url,
            'robots' => $this->robots,
            'image' => $this->image,
            'type' => $this->schema_type,
            'extra' => $extra,
        ]));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function nonEmpty(array $data): array
    {
        return array_filter($data, fn (mixed $value): bool => $this->filled($value));
    }

    private function filled(mixed $value): bool
    {
        return ! ($value === null || $value === '' || $value === []);
    }
}
