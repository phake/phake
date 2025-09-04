<?php

declare(strict_types=1);

namespace Phake\Matchers;

interface IPropertyHookMatcher
{
    public function matches(string $property, string $hook, array &$args): bool;

    public function getProperty(): string;

    public function getHook(): string;
}
