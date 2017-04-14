<?php

use PHPUnit\Framework\TestCase;

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
        $this->assertInstanceOf('Phake_Client_PHPUnit6', $client);
    }
}
