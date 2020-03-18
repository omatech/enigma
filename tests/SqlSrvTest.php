<?php

namespace Omatech\Enigma\Tests;

class SqlSrvTest extends EncryptModelTestBase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('database.default', 'sqlsrv');
    }
}
