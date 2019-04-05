<?php

namespace Lamoda\Codeception\Tests\Extension\AdapterFactory;

use Codeception\Exception\ModuleConfigException;
use Codeception\Test\Unit;
use Lamoda\Codeception\Extension\AdapterFactory\AwsS3AdapterFactory;
use League\Flysystem\AwsS3v3\AwsS3Adapter;

class AwsS3AdapterFactoryTest extends Unit
{
    public function testCreateAdapter()
    {
        $adapter = AwsS3AdapterFactory::createAdapter(['bucket' => 'your-bucket', 'region' => 'your-region', 'version' => 'latest']);
        $this->assertInstanceOf(AwsS3Adapter::class, $adapter);
    }

    /**
     * @param array $config
     *
     * @throws \Codeception\Exception\ModuleConfigException
     *
     * @dataProvider dataFailedCreateAdapter
     */
    public function testFailedCreateAdapter($config)
    {
        $expectedExceptionMessage = 'Configuration for s3 is broken. Configuration must contains bucket parameter with string type.';
        $this->expectException(ModuleConfigException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        AwsS3AdapterFactory::createAdapter($config);
    }

    public function dataFailedCreateAdapter()
    {
        return [
            [
                []
            ],
            [
                ['bucket' => ['str']]
            ]
        ];
    }
}
