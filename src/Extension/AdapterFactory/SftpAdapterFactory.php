<?php

namespace Lamoda\Codeception\Extension\AdapterFactory;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Sftp\SftpAdapter;

class SftpAdapterFactory implements AdapterFactoryInterface
{
    /**
     * @param array $config
     *
     * @return AdapterInterface
     */
    public static function createAdapter(array $config)
    {
        return new SftpAdapter($config);
    }
}
