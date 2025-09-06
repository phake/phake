<?php

declare(strict_types=1);

namespace PhakeTest;

class TrueType
{
    public function trueParam(true $param)
    {
    }

    public function trueReturn(): true
    {
    }
}
