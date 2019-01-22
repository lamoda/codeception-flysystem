<?php

namespace Lamoda\Codeception\Tests\Extension\AdapterFactory;

use Codeception\Test\Unit;
use Lamoda\Codeception\Extension\AdapterFactory\WebdavAdapterFactory;
use League\Flysystem\WebDAV\WebDAVAdapter;

class WebdavAdapterFactoryTest extends Unit
{
    public function testCreateAdapter()
    {
        $adapter = WebdavAdapterFactory::createAdapter(['baseUri' => 'base/uri']);
        $this->assertInstanceOf(WebDAVAdapter::class, $adapter);
    }
}
