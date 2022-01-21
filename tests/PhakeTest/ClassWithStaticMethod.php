<?php

class PhakeTest_ClassWithStaticMethod
{
    public static function ask() {
        return 'Asked';
    }

    public static function askWho($who) {
        return $who;
    }
}
