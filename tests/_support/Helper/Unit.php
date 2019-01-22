<?php

namespace Helper;

class Unit extends \Codeception\Module
{
    /**
     * @param object $object
     * @param string $propertyName
     *
     * @throws \ReflectionException
     *
     * @return mixed $value
     */
    public function getProtectedProperty($object, $propertyName)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $value = $property->getValue($object);
        $property->setAccessible(false);

        return $value;
    }
}
