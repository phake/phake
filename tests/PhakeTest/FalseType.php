<?php

declare(strict_types=1);

namespace PhakeTest;

class FalseType
{
    public function falseParam(false $param)
    {
    }

    public function falseReturn(): false
    {
    }
}
