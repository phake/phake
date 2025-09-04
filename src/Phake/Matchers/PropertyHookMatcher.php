<?php

declare(strict_types=1);

namespace Phake\Matchers;

class PropertyHookMatcher implements IPropertyHookMatcher
{
    public function __construct(
        private string $expectedProperty,
        private string $expectedHook,
        private ?IChainableArgumentMatcher $argumentMatcherChain = null
    ) {
    }

    public function matches(string $property, string $hook, array &$args): bool
    {
        try {
            $this->assertMatches($property, $hook, $args);

            return true;
        } catch (\Phake\Exception\PropertyHookMatcherException|\Phake\Exception\MethodMatcherException $e) {
            return false;
        }
    }

    public function assertMatches(string $property, string $hook, array &$args): void
    {
        if ($this->expectedProperty != $property || $this->expectedHook != $hook) {
            throw new \Phake\Exception\PropertyHookMatcherException("Expected hook {$this->expectedProperty}::{$this->expectedHook} but received {$property}::{$hook}.");
        }

        $this->doArgumentsMatch($args);
    }

    /**
     * Determines whether or not given arguments match the argument matchers configured in the object.
     *
     * Throws an exception with a description if the arguments do not match.
     *
     * @throws \Phake\Exception\PropertyHookMatcherException
     */
    private function doArgumentsMatch(array &$args): void
    {
        if (null !== $this->argumentMatcherChain) {
            try {
                $this->argumentMatcherChain->doArgumentsMatch($args);
            } catch (\Phake\Exception\PropertyHookMatcherException $e) {
                $position = $e->getArgumentPosition() + 1;
                throw new \Phake\Exception\PropertyHookMatcherException(trim("Argument #{$position} failed test\n" . $e->getMessage()), $e);
            }
        } elseif (0 != count($args)) {
            throw new \Phake\Exception\PropertyHookMatcherException('No matchers were given to Phake::when(), but arguments were received by this method.');
        }
    }

    public function getProperty(): string
    {
        return $this->expectedProperty;
    }

    public function getHook(): string
    {
        return $this->expectedHook;
    }
}
