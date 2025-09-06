<?php

declare(strict_types=1);

namespace PhakeTest;

enum SomeEnum: string
{
    case A = 'a';
    case B = 'b';
}

class EnumType
{
    public function enumReturn(): SomeEnum
    {
    }
}
