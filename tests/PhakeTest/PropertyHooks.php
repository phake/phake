<?php

declare(strict_types=1);

class PhakeTest_PropertyHooks
{
    public string $publicPropWithHooks = 'foobar' {
        get => $this->publicPropWithHooks;
        set => $value; 
    }
}
