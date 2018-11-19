<?php

namespace Lamoda\Codeception\Extension\AdapterFactory;

use League\Flysystem\AdapterInterface;

interface AdapterFactoryInterface
{
    /**
     * @param array $config
     *
     * @return AdapterInterface
     */
    public static function createAdapter(array $config);
}
