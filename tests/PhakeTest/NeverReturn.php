<?php

declare(strict_types=1);

namespace PhakeTest;

class NeverReturn
{
    public function neverReturn(): never
    {
        die('Asta la vista baby');
    }
}
