<?php

namespace Lamoda\Codeception\Tests\Extension;

use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\ModuleContainer;
use Codeception\Test\Unit;
use Lamoda\Codeception\Extension\AdapterFactory\SftpAdapterFactory;
use Lamoda\Codeception\Extension\AdapterFactory\WebdavAdapterFactory;
use Lamoda\Codeception\Extension\FlySystemModule;
use Exception;
use League\Flysystem\Filesystem;
use League\Flysystem\Sftp\SftpAdapter;
use League\Flysystem\WebDAV\WebDAVAdapter;

class FlySystemModuleTest extends Unit
{
    /** @var \UnitTester */
    protected $tester;

    public function testInitializeWithEmptyConfig()
    {
        $moduleContainer = $this->createMock(ModuleContainer::class);
        $module = new FlySystemModule($moduleContainer);
        $module->_initialize();
    }

    /**
     * @param array $config
     *
     * @throws \Codeception\Exception\ModuleConfigException
     * @throws \ReflectionException
     *
     * @dataProvider dataGetFileSystem
     */
    public function testGetFileSystem($config)
    {
        $moduleContainer = $this->createMock(ModuleContainer::class);
        $module = new FlySystemModule($moduleContainer, $config);
        $module->_initialize();

        /** @var Filesystem $flySystemSftp */
        $flySystemSftp = $this->tester->getProtectedProperty(
            $module->getFileSystem('sftp adapter'),
            'flySystem'
        );
        $this->assertInstanceOf(SftpAdapter::class, $flySystemSftp->getAdapter());

        /** @var Filesystem $flySystemWebdav */
        $flySystemWebdav = $this->tester->getProtectedProperty(
            $module->getFileSystem('webdav adapter'),
            'flySystem'
        );
        $this->assertInstanceOf(WebDAVAdapter::class, $flySystemWebdav->getAdapter());
    }

    public function dataGetFileSystem()
    {
        return [
            [
                [
                    'adapters' => [
                        'sftp adapter' => [
                            'builderAdapter' => SftpAdapterFactory::class,
                            'config' => [],
                        ],
                        'webdav adapter' => [
                            'builderAdapter' => WebdavAdapterFactory::class,
                            'config' => ['baseUri' => 'base/uri'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $config
     * @param string $expectedExceptionMessage
     *
     * @throws \Codeception\Exception\ModuleConfigException
     *
     * @dataProvider dataInitializationFailed
     */
    public function testInitializationFailed($config, $expectedExceptionMessage)
    {
        $this->expectException(ModuleConfigException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $moduleContainer = $this->createMock(ModuleContainer::class);
        $module = new FlySystemModule($moduleContainer, $config);
        $module->_initialize();
    }

    public function dataInitializationFailed()
    {
        return [
            [
                [
                    'adapters' => [
                        'sftp adapter' => [
                            'builderAdapter' => ['This is not string'],
                            'config' => '_',
                        ],
                    ],
                ],
                <<<MSG
Lamoda\Codeception\Extension\FlySystemModule module is not configured!
 
Configuration for sftp adapter is broken. Expected type is string for parameter builderAdapter
MSG
            ],
            [
                [
                    'adapters' => [
                        'sftp adapter' => [
                            'builderAdapter' => SftpAdapterFactory::class,
                            'config' => 'This is not array',
                        ],
                    ],
                ],
                <<<MSG
Lamoda\Codeception\Extension\FlySystemModule module is not configured!
 
Configuration for sftp adapter is broken. Expected type is array for parameter config
MSG
            ],
            [
                [
                    'adapters' => [
                        'sftp adapter' => [
                            'builderAdapter' => 'NonexistentClass',
                            'config' => [],
                        ],
                    ],
                ],
                <<<MSG
Lamoda\Codeception\Extension\FlySystemModule module is not configured!
 
Adapter NonexistentClass does not exist, please use another one
MSG
            ],
            [
                [
                    'adapters' => [
                        'sftp adapter' => [
                            'builderAdapter' => Exception::class,
                            'config' => [],
                        ],
                    ],
                ],
                <<<MSG
Lamoda\Codeception\Extension\FlySystemModule module is not configured!
 
Adapter factory class must implement Lamoda\Codeception\Extension\AdapterFactory\AdapterFactoryInterface, Exception adapter factory given
MSG
            ],
        ];
    }
}
