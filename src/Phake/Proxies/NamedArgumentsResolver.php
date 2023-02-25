<?php

declare(strict_types=1);

namespace Phake\Proxies;

/*
 * Phake - Mocking Framework
 *
 * Copyright (c) 2010-2022, Mike Lively <m@digitalsandwich.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *  *  Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *  *  Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *  *  Neither the name of Mike Lively nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    Phake
 * @author     Pierrick Charron <pierrick@adoy.net>
 * @copyright  2010 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.digitalsandwich.com/
 */

/**
 * Resovle named arguments as positional arguments
 *
 * @author Pierrick Charron <pierrick@adoy.net>
 */
trait NamedArgumentsResolver
{
    /**
     * @psalm-suppress TypeDoesNotContainType
     *
     * @param \Phake\IMock|class-string $object
     * @param string $method
     * @param array $arguments
     *
     * @return array
     */
    private function resolveNamedArguments(\Phake\IMock|string $object, string $method, array $arguments): array
    {
        $positionalArguments = [];
        $namedArguments      = [];
        foreach ($arguments as $key => $arg) {
            if (is_int($key)) {
                $positionalArguments[$key] = $arg;
            } else {
                $namedArguments[$key] = $arg;
            }
        }

        if (!empty($namedArguments)) {
            try {
                $parameters = (new \ReflectionClass($object))->getMethod($method)->getParameters();
            } catch (\ReflectionException $e) {
                $parameters = [];
            }
            foreach ($parameters as $position => $parameter) {
                if (empty($namedArguments)) {
                    break;
                }
                $name = $parameter->getName();
                if ($parameter->isVariadic()) {
                    $positionalArguments[count($positionalArguments)] = $namedArguments;
                } elseif (array_key_exists($name, $namedArguments)) {
                    $positionalArguments[$position] = $namedArguments[$name];
                    unset($namedArguments[$name]);
                } elseif ($parameter->isOptional() && !array_key_exists($position, $positionalArguments)) {
                    $positionalArguments[$position] = $parameter->getDefaultValue();
                }
            }
        }

        return $positionalArguments;
    }
}
