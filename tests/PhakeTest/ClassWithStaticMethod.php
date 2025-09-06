<?php

declare(strict_types=1);

namespace PhakeTest;

class ClassWithStaticMethod
{
    public static function ask()
    {
        return 'Asked';
    }

    public static function askWho($who)
    {
        return $who;
    }
}
