<?php

declare(strict_types=1);

namespace Zarbin\Seo\Http\Controllers;

use Illuminate\Http\Response;
use Zarbin\Seo\Generators\RobotsTxtGenerator;

final class RobotsTxtController
{
    public function __invoke(): Response|string
    {
        return response((new RobotsTxtGenerator)->render(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
