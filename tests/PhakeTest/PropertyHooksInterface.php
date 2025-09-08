<?php

declare(strict_types=1);

namespace PhakeTest;

interface PropertyHooksInterface
{
    public string $stringWithGet { get; }
    public string $stringWithSet { set; }
    public string $stringWithGetAndSet { get; set; }
}
