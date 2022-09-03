<?php

declare(strict_types=1);

class PhakeTest_FalseType
{
    public function falseParam(false $param)
    {
    }

    public function falseReturn(): false
    {
    }
}
