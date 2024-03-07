<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

class CarbonSynth extends Synth
{
    public static $types = [
        'native' => DateTime::class,
        'nativeImmutable' => DateTimeImmutable::class,
        'carbon' => Carbon::class,
        'carbonImmutable' => CarbonImmutable::class,
        'illuminate' => \Illuminate\Support\Carbon::class,
    ];

    public static $key = 'cbn';

    public static function match($target)
    {
        foreach (static::$types as $type => $class) {
            if ($target instanceof $class) {
                return true;
            }
        }

        return false;
    }

    public static function matchByType($type)
    {
        return is_subclass_of($type, DateTimeInterface::class);
    }

    public function dehydrate($target)
    {
        return [
            $target->format(DateTimeInterface::ATOM),
            ['type' => array_search(get_class($target), static::$types)],
        ];
    }

    public static function hydrateFromType($type, $value)
    {
        if ($value === '' || $value === null) {
            return null;
        }

        return new $type($value);
    }

    public function hydrate($value, $meta)
    {
        if ($value === '' || $value === null) {
            return null;
        }

        return new static::$types[$meta['type']]($value);
    }
}
