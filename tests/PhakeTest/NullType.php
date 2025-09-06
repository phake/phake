<?php

declare(strict_types=1);

namespace PhakeTest;

class NullType
{
    public function nullParam(null $param)
    {
    }

    public function nullReturn(): null
    {
    }
}
