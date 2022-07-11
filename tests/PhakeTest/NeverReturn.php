<?php

declare(strict_types=1);

class PhakeTest_NeverReturn
{
    public function neverReturn(): never
    {
        die('Asta la vista baby');
    }
}
