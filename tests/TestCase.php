<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    // Laravel 11 automatically discovers bootstrap/app.php.
    // No createApplication() override needed.

    protected function setUp(): void
    {
        parent::setUp();

        // Disable FK enforcement for SQLite so that FK-fix migrations
        // (which use MySQL-specific named-FK syntax) can be skipped
        // without breaking test data creation.
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        }
    }
}
