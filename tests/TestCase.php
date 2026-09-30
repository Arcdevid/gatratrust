<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        // Stop before any test can run migrations against an application database.
        if (! $app->environment('testing')
            || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:'
            || config('database.connections.sqlite.url')) {
            throw new \RuntimeException('Tests require SQLite :memory: with no database URL.');
        }

        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);

        return $app;
    }
}
