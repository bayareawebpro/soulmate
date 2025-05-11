<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate\Tests\Feature;

use BayAreaWebPro\Soulmate\Soulmate;
use BayAreaWebPro\Soulmate\SoulmateService;
use BayAreaWebPro\Soulmate\SoulmateServiceProvider;
use BayAreaWebPro\Soulmate\Tests\TestCase;

class ProviderTest extends TestCase
{
    /** @noinspection PhpParamsInspection */
    public function test_provider_is_registered(): void
    {
        $this->assertInstanceOf(
            SoulmateServiceProvider::class,
            $this->app->getProvider(SoulmateServiceProvider::class),
            'Provider is registered with container.'
        );
    }

    /** @noinspection PhpParamsInspection */
    public function test_provider_declares_provided(): void
    {
        $provider = $this->app->getProvider(SoulmateServiceProvider::class);

        $this->assertTrue(in_array('package-name', $provider->provides()), 'Provider declares provided services.');
    }

    public function test_container_can_resolve_instance(): void
    {
        $this->assertInstanceOf(
            SoulmateService::class,
            $this->app->make('package-name'),
            'Container can make instance of service.'
        );
    }

    public function test_alias_can_resolve_instance(): void
    {
        $this->assertInstanceOf(
            SoulmateService::class,
            \Soulmate::getFacadeRoot(),
            'Alias class can make instance of service.'
        );
    }

    public function test_facade_can_resolve_instance(): void
    {
        $this->assertInstanceOf(
            SoulmateService::class,
            Soulmate::getFacadeRoot(),
            'Facade can make instance of service.'
        );
    }
}
