<?php

declare(strict_types=1);

namespace PhakeTest;

class ToStringMethod implements \Stringable
{
    public function __toString(): string
    {
    }
}
