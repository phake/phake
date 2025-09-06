<?php

declare(strict_types=1);

namespace PhakeTest;

class PropertyHooks
{
    public string $publicPropWithHooks = 'foobar' {
        get => $this->publicPropWithHooks;
        set => $value; 
    }
}
