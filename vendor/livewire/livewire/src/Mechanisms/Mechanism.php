<?php

namespace Livewire\Mechanisms;

abstract class Mechanism
{
    public function register()
    {
        app()->instance(static::class, $this);
    }

    public function boot()
    {
        //
    }
}
