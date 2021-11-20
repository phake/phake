<?php

class PhakeTest_IntersectionTypes
{
    public function intersectionParam(Countable & ArrayAccess $param) {

    }

    public function intersectionReturn(): Countable & ArrayAccess {

    }
}
