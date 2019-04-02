<?php

namespace Lamoda\Codeception\Extension\AdapterFactory;

use Codeception\Exception\ModuleConfigException;
use League\Flysystem\AdapterInterface;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;

class AwsS3AdapterFactory implements AdapterFactoryInterface
{
    /**
     * @param array $config
     *
     * @return AdapterInterface
     */
    public static function createAdapter(array $config)
    {
        if(!isset($config['bucket']) or !is_string($config['bucket'])) {
            $message = sprintf('Configuration for s3 is broken. Configuration must contains bucket parameter with string type.');
            throw new ModuleConfigException(__CLASS__, $message);
        }

        $client = new S3Client($config);

        return new AwsS3Adapter($client, $config['bucket']);
    }
}
