<?php

declare(strict_types=1);

namespace PhakeTest;

use ArrayAccess;
use Countable;

class IntersectionTypes
{
    public function intersectionParam(Countable & ArrayAccess $param)
    {
    }

    public function intersectionReturn(): Countable & ArrayAccess
    {
    }
}
