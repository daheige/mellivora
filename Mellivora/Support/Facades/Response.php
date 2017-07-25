<?php

namespace Mellivora\Support\Facades;

/**
 * @see \Mellivora\Http\Response
 */
class Response extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'response';
    }
}
