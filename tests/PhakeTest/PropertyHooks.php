<?php

declare(strict_types=1);

namespace PhakeTest;

class PropertyHooks
{
    public string $publicPropWithHooks = 'foobar';

    public string $stringWithDefaultValue = 'default';
    public string $stringWithoutDefaultValue;

    public int $intWithDefaultValue = 42;
    public int $intWithoutDefaultValue;

}
