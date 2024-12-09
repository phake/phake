<?php

declare(strict_types=1);

enum SomeEnum: string
{
    case A = 'a';
    case B = 'b';
}

class PhakeTest_EnumType
{
    public function enumReturn(): SomeEnum
    {
    }
}
