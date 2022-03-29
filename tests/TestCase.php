<?php

namespace ShaanXiNetworkFreight\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $testUser;
    protected $config;

    public function setUp()
    {

    }

    protected function mockDingClient($client = null)
    {
    }

    protected function matchContent($content)
    {
        return true;
    }

}
