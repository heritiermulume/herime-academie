<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Bootstraps the application for testing.
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Désactiver le SSO pendant les tests pour éviter les redirections externes
        config()->set('services.sso.enabled', false);
        config()->set('services.sso.force_local_logout', true);
        config()->set('app.url', 'https://academie.test');
    }
}
