<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

final class SeoUiAuthorization
{
    public static function authorize(): void
    {
        $gate = UiConfig::gate();

        if ($gate === null) {
            return;
        }

        try {
            if (Gate::allows($gate)) {
                return;
            }
        } catch (Throwable) {
            return;
        }

        if (function_exists('abort')) {
            abort(403);
        }

        throw new HttpException(403);
    }
}
