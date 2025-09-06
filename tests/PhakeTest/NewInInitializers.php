<?php

declare(strict_types=1);

namespace PhakeTest;

class NewInInitializers
{
    public function simpleNew($param = new \stdClass())
    {
    }

    public function newWithParams($param = new \stdClass('Foo bar baz', 10))
    {
    }
}
