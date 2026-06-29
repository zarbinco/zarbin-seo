<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Zarbin\Seo\Concerns\HasSeoMeta;
use Zarbin\Seo\Models\SeoMeta;
use Zarbin\Seo\Tests\Concerns\CreatesSeoMetaTable;
use Zarbin\Seo\Tests\TestCase;

final class HasSeoMetaTest extends TestCase
{
    use CreatesSeoMetaTable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSeoMetaTable();
        Schema::create('seo_meta_test_posts', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->timestamps();
        });
    }

    public function test_seo_metas_relation_works(): void
    {
        $post = HasSeoMetaPost::query()->create(['title' => 'Post']);

        $post->seoMetas()->create([
            'locale' => 'fa',
            'title' => 'Relation title',
        ]);

        $this->assertSame(1, $post->seoMetas()->count());
        $this->assertSame('Relation title', $post->seoMetas()->first()?->title);
    }

    public function test_save_seo_meta_creates_and_updates_locale_record(): void
    {
        $post = HasSeoMetaPost::query()->create(['title' => 'Post']);

        $created = $post->saveSeoMeta([
            'title' => 'Created title',
            'canonical' => 'https://example.com/post',
        ], 'fa');
        $updated = $post->saveSeoMeta([
            'title' => 'Updated title',
        ], 'fa');

        $this->assertInstanceOf(SeoMeta::class, $created);
        $this->assertSame($created->getKey(), $updated->getKey());
        $this->assertSame('Updated title', $post->seoMetaForLocale('fa')?->title);
        $this->assertSame(1, $post->seoMetas()->count());
    }

    public function test_seo_meta_for_locale_returns_record(): void
    {
        $post = HasSeoMetaPost::query()->create(['title' => 'Post']);

        $post->saveSeoMeta(['title' => 'English title'], 'en');

        $this->assertSame('English title', $post->seoMetaForLocale('en')?->title);
        $this->assertNull($post->seoMetaForLocale('fa'));
    }

    public function test_delete_seo_meta_deletes_record(): void
    {
        $post = HasSeoMetaPost::query()->create(['title' => 'Post']);

        $post->saveSeoMeta(['title' => 'English title'], 'en');

        $this->assertTrue($post->deleteSeoMeta('en'));
        $this->assertFalse($post->deleteSeoMeta('en'));
        $this->assertSame(0, $post->seoMetas()->count());
    }
}

final class HasSeoMetaPost extends Model
{
    use HasSeoMeta;

    protected $table = 'seo_meta_test_posts';

    /**
     * @var array<int, string>
     */
    protected $fillable = ['title'];
}
