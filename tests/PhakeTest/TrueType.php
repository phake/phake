<?php

declare(strict_types=1);

class PhakeTest_TrueType
{
    public function trueParam(true $param)
    {
    }

    public function trueReturn(): true
    {
    }
}
