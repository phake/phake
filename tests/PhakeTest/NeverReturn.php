<?php

class PhakeTest_NeverReturn
{
    public function neverReturn(): never
    {
        die('Asta la vista baby');
    }
}
