<?php

namespace Lamoda\Codeception\Extension;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\TestRuntimeException;
use Codeception\Module as CodeceptionModule;
use Lamoda\Codeception\Extension\AdapterFactory\AdapterFactoryInterface;
use League\Flysystem\Filesystem as FlySystem;

class FlySystemModule extends CodeceptionModule
{
    /** @var FileSystem[] */
    protected $instances = [];

    /** @var string */
    protected $path;

    /** @var array */
    protected $requiredParametersConfig = [
        'builderAdapter' => 'string',
        'config' => 'array',
    ];

    /**
     * @throws ModuleConfigException
     */
    public function _initialize()
    {
        if (isset($this->config['adapters']) && is_array($this->config['adapters'])) {
            foreach ($this->config['adapters'] as $name => $adapterConfig) {
                $this->validateAdapterConfig($adapterConfig, $name);
                $this->instances[$name] = $this->createFileSystemHelper($adapterConfig);
            }
        }
        parent::_initialize();
    }

    /**
     * @param string $name
     *
     * @return FileSystem
     */
    public function getFileSystem($name)
    {
        if (!isset($this->instances[$name])) {
            throw new TestRuntimeException("Undefined adapter $name");
        }

        return $this->instances[$name];
    }

    /**
     * @param array $config
     * @param string $name
     *
     * @throws ModuleConfigException
     */
    private function validateAdapterConfig(array $config, $name)
    {
        foreach ($this->requiredParametersConfig as $parameter => $type) {
            if (!isset($parameter, $config)) {
                $message = sprintf('Configuration for %s is broken. Configuration must contains %s', $name, implode($this->requiredParametersConfig));
                throw new ModuleConfigException(__CLASS__, $message);
            }
            switch ($type) {
                case 'array':
                    $valid = is_array($config[$parameter]);
                    break;
                default:
                    $valid = is_string($config[$parameter]);
            }

            if (!$valid) {
                $message = sprintf('Configuration for %s is broken. Expected type is %s for parameter %s', $name, $type, $parameter);
                throw new ModuleConfigException(__CLASS__, $message);
            }
        }
    }

    /**
     * @param string $className
     *
     * @throws ModuleConfigException
     *
     * @return AdapterFactoryInterface
     */
    private function createAdapterFactory($className)
    {
        if (!class_exists($className)) {
            $message = sprintf('Unexpected error happened on creation adapter factory, %s', $className);
            throw new ModuleConfigException(__CLASS__, $message);
        }

        $adapterFactory = new $className();

        if (!$adapterFactory instanceof AdapterFactoryInterface) {
            $message = sprintf('Adapter factory class must implement %s, %s adapter factory given', AdapterFactoryInterface::class, $className);
            throw new ModuleConfigException(__CLASS__, $message);
        }

        return $adapterFactory;
    }

    /**
     * @param array $adapterConfig
     *
     * @throws ModuleConfigException
     *
     * @return FileSystem
     */
    private function createFileSystemHelper(array $adapterConfig)
    {
        $adapterFactoryClass = $adapterConfig['builderAdapter'];
        $config = $adapterConfig['config'];
        $builderAdapter = $this->createAdapterFactory($adapterFactoryClass);
        $adapter = $builderAdapter::createAdapter($config);
        $fileSystem = new FlySystem($adapter);

        return new FileSystem($fileSystem);
    }
}
