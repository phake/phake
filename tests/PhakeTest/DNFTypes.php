<?php

declare(strict_types=1);

namespace PhakeTest;

use Countable;
use Traversable;

class DNFTypes
{
    public function dnfParam((Traversable&Countable)|false $param)
    {
    }

    public function dnfReturn(): (Traversable&Countable)|false
    {
    }
}
