<?php

namespace Tests\Unit\Services;

use App\Services\SlugRedirectResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SlugRedirectResolverTest extends TestCase
{
    use DatabaseTransactions;

    private SlugRedirectResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new SlugRedirectResolver();
    }

    public function test_resolve_returns_null_when_no_redirect_recorded(): void
    {
        $this->assertNull($this->resolver->resolve('brand', 'zz-does-not-exist'));
    }

    public function test_record_then_resolve_returns_new_slug(): void
    {
        $this->resolver->record('brand', 'zz-other-14', 'zz-other-fragrances');

        $this->assertSame('zz-other-fragrances', $this->resolver->resolve('brand', 'zz-other-14'));
    }

    public function test_record_is_idempotent_for_same_old_slug(): void
    {
        $this->resolver->record('brand', 'zz-apple-2', 'zz-apple-tablets');
        $this->resolver->record('brand', 'zz-apple-2', 'zz-apple-tablets-updated');

        $this->assertSame('zz-apple-tablets-updated', $this->resolver->resolve('brand', 'zz-apple-2'));
    }

    public function test_resolve_is_scoped_by_type(): void
    {
        $this->resolver->record('brand', 'zz-other', 'zz-other-brand-qualified');

        $this->assertNull($this->resolver->resolve('subcategory', 'zz-other'));
    }

    public function test_record_is_idempotent_when_called_twice_within_the_same_second(): void
    {
        // Regression test: record() must not decide insert-vs-update from
        // update()'s affected-row count. Re-recording an identical
        // (type, old_slug, new_slug) triple within the same second updates
        // 0 rows in MySQL (nothing differs; updated_at has only second
        // precision), which used to fall through to insert() and violate
        // the unique(['sluggable_type', 'old_slug']) constraint.
        $this->resolver->record('brand', 'zz-same-second', 'zz-same-second-new');
        $this->resolver->record('brand', 'zz-same-second', 'zz-same-second-new');

        $this->assertSame('zz-same-second-new', $this->resolver->resolve('brand', 'zz-same-second'));

        $count = DB::table('slug_redirects')
            ->where('sluggable_type', 'brand')
            ->where('old_slug', 'zz-same-second')
            ->count();

        $this->assertSame(1, $count);
    }
}
