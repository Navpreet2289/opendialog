<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use OpenDialogAi\Core\Graph\DGraph\DGraphClient;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * @var bool Whether DGraph has been initialised or not
     */
    private $dgraphInitialised = false;

    /**
     * Runs migrations on the sqlite database
     */
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');

        try {
            $env = parse_ini_file(__DIR__ . '/../.env');
            if (isset($env['DGRAPH_URL'])) {
                $this->app['config']->set('opendialog.core.DGRAPH_URL', $env['DGRAPH_URL']);
            }
            dd($env['DGRAPH_URL']);
        } catch (\Exception $e) {
            //
        }
    }

    protected function initDDgraph(): void
    {
        if (!$this->dgraphInitialised) {
            try {
                /** @var DGraphClient $client */
                $client = $this->app->make(DGraphClient::class);
                $client->dropSchema();
                $client->initSchema();
                $this->dgraphInitialised = true;
            } catch (\Exception $e) {
                print_r("DGRAPH Error: " .
                    $e->getMessage() .
                    " ( Make sure test DGRAPH containers are running from /tests/docker-compose.yml )");
            }
        }
    }

    protected function webchatSetup(): void
    {
        $this->artisan('webchat:setup');
    }
}
