<?php

namespace Mellivora\Facades;

use Mellivora\Application\App;
use Mellivora\Exception;

/**
 * 仿 Laravel 的 Facades 设计的伪装类
 *
 * 可方便的用于类的伪装，进行快速调用
 */
class Facade
{
    /**
     * @var Mellivora\Application\App
     */
    protected static $app;

    /**
     * The resolved object instances.
     *
     * @var array
     */
    protected static $resolvedInstance;

    /**
     * Get the root object behind the facade.
     *
     * @return mixed
     */
    public static function getFacadeRoot()
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor());
    }

    /**
     * Get the registered name of the component.
     *
     * @throws Mellivora\Exception
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        throw new Exception('Facade does not implement getFacadeAccessor method.');
    }

    /**
     * Resolve the facade root instance from the container.
     *
     * @param  string|object $name
     * @return mixed
     */
    protected static function resolveFacadeInstance($name)
    {
        if (is_object($name)) {
            return $name;
        }

        if (isset(static::$resolvedInstance[$name])) {
            return static::$resolvedInstance[$name];
        }

        return static::$resolvedInstance[$name] = static::$app->getContainer()->get($name);
    }

    /**
     * Clear a resolved facade instance.
     *
     * @param string $name
     */
    public static function clearResolvedInstance($name)
    {
        unset(static::$resolvedInstance[$name]);
    }

    /**
     * Clear all of the resolved instances.
     */
    public static function clearResolvedInstances()
    {
        static::$resolvedInstance = [];
    }

    /**
     * Get the application instance behind the facade.
     *
     * @return Mellivora\Application\App
     */
    public static function getFacadeApplication()
    {
        return static::$app;
    }

    /**
     * Set the application instance.
     *
     * @param Mellivora\Application\App $app
     */
    public static function setFacadeApplication(App $app)
    {
        static::$app = $app;
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string                $method
     * @param  array                 $args
     * @throws Mellivora\Exception
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeRoot();

        if (!$instance) {
            throw new Exception('A facade root has not been set.');
        }

        return $instance->$method(...$args);
    }
}
