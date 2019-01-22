<?php

namespace Lamoda\Codeception\Tests\Extension\AdapterFactory;

use Codeception\Test\Unit;
use Lamoda\Codeception\Extension\AdapterFactory\SftpAdapterFactory;
use League\Flysystem\Sftp\SftpAdapter;

class SftpAdapterFactoryTest extends Unit
{
    public function testCreateAdapter()
    {
        $adapter = SftpAdapterFactory::createAdapter([]);
        $this->assertInstanceOf(SftpAdapter::class, $adapter);
    }
}
