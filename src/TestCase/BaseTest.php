<?php

namespace WeeWorxxSDK\SharedResources\TestCase;

use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase as BaseTestCase;
use WeeWorxxSDK\SharedResources\SharedResourceServiceProvider;

abstract class BaseTest extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            SharedResourceServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // ==============================================================
        // Only execute if user has used the RefreshDatabase trait
        // otherwise, ignore this.
        // ==============================================================
        if (method_exists($this, 'refreshDatabase')) {
            $this->refreshDatabase();
        }

        $this->resetMysqlSchema();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');

        $app['config']->set('database.connections.testing', [
            'driver' => 'mysql',
            'host' => 'mysql',
            'port' => '3306',
            'database' => 'weeworx_test', // ✅ use a separate DB for tests
            'username' => 'weeworx_user',
            'password' => 'w33w0rxx123',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);
    }


    protected function resetMysqlSchema(): void
    {
        $schemaPath = __DIR__ . '/sqldumps/weeworx.sql';

        if (!file_exists($schemaPath)) {
            throw new \RuntimeException('schema.sql not found at: ' . $schemaPath);
        }

        // Reconnect to 'testing' DB
        DB::disconnect('testing');
        DB::purge('testing');

        config()->set('database.default', 'testing');

        $sql = file_get_contents($schemaPath);
        foreach (explode(";", $sql) as $statement) {
            $trimmed = trim($statement);
            if (!empty($trimmed)) {
                DB::statement($trimmed);
            }
        }
    }


}
