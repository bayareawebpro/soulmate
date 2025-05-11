<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin SoulmateService
 */
class Soulmate extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SoulmateService::class;
    }
}
