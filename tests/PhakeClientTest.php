<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;

class PhakeClientTest extends TestCase
{
    protected function setup()
    {
        // unset the $client in Phake
        $refClass = new ReflectionClass('Phake');
        $clientProperty = $refClass->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue(null);
    }

    public function testAutoDetectsPHPUnitClient()
    {
        $client = Phake::getClient();
        $this->assertInstanceOf('Phake_Client_PHPUnit' . substr(Version::id(), 0, 1), $client);
    }
}
