<?php

namespace Omatech\Enigma\Tests;

class SqliteTest extends EncryptModelTestBase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('database.default', 'sqlite');
    }
}
