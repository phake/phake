<?php

declare(strict_types=1);

class PhakeTest_NullType
{
    public function nullParam(null $param)
    {
    }

    public function nullReturn(): null
    {
    }
}
