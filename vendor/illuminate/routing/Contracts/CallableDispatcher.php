<?php

namespace Illuminate\Routing\Contracts;

use Illuminate\Routing\Route;

interface CallableDispatcher
{
    /**
     * Dispatch a request to a given callable.
     *
     * @param  callable  $callable
     * @return mixed
     */
    public function dispatch(Route $route, $callable);
}
