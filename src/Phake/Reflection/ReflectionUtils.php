<?php

class Phake_Reflection_ReflectionUtils
{
    public static function getTypeName(\ReflectionType $type): string
    {
        if (version_compare(phpversion(), '7.1.0') < 0) {
            return (string)$type;
        } else {
            /** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
            return $type->getName();
        }
    }
}