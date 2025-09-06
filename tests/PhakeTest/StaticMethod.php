<?php

declare(strict_types=1);

namespace PhakeTest;

class StaticMethod
{
    public $className = \ClassWithStaticMethod::class;

    public function askSomething()
    {
        $className = $this->className;
        return $className::ask();
    }
}
