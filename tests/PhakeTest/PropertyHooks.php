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

    public readonly int $readOnlyProperty;

    final public string $finalProperty = 'final';

    public string $hookWithFinalSet {
        get => $this->hookWithFinalSet;
        final set => $value;
    }

    public string $virtualReadOnly {
        get => 'virtualRO';
    }
    public string $virtualWriteOnly {
        set { $this->stringWithoutDefaultValue = $value; }
    }
}
