<?php

namespace Lamoda\Codeception\Extension\AdapterFactory;

use League\Flysystem\AdapterInterface;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Sabre\DAV\Client;

class WebdavAdapterFactory implements AdapterFactoryInterface
{
    /**
     * @param array $config
     *
     * @return AdapterInterface
     */
    public static function createAdapter(array $config)
    {
        $client = new Client($config);

        return new WebDAVAdapter($client);
    }
}
