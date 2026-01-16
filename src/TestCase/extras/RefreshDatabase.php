<?php

namespace WeeWorxxSDK\SharedResources\TestCase\Extras;

use Illuminate\Support\Facades\DB;

trait RefreshDatabase
{
    protected function refreshDatabase(): void
    {
        $schemaPath = realpath(__DIR__ . '/../sqldumps/weeworx.sql');

        if (!$schemaPath || !file_exists($schemaPath)) {
            throw new \RuntimeException('SQL dump not found at: ' . $schemaPath);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $sql = file_get_contents($schemaPath);
        foreach (explode(';', $sql) as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                DB::statement($statement);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

}
