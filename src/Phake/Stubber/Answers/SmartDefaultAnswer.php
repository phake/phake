<?php
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
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2010 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.digitalsandwich.com/
 */

declare(strict_types=1);

namespace Phake\Stubber\Answers;

/**
 * Returns the proper default value for a method based on the return type.
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class SmartDefaultAnswer implements \Phake\Stubber\IAnswer
{
    public function processAnswer(mixed $answer): void
    {
    }

    private function getReturnTypeResult(?\ReflectionType $returnType, \ReflectionClass $class): mixed
    {
        if (null === $returnType) {
            return null;
        } elseif ($returnType instanceof \ReflectionNamedType) {
            switch ($typeName = $returnType->getName()) {
                case 'int':
                    return 0;
                case 'float':
                    return 0.0;
                case 'string':
                    return '';
                case 'bool':
                case 'false':
                    return false;
                case 'true':
                    return true;
                case 'array':
                    return [];
                case 'callable':
                    return function () {};
                case 'self':
                    return \Phake::mock($class->getName());
                case 'null':
                case 'void':
                case 'never':
                    return null;
                default:
                    if (function_exists('enum_exists') && enum_exists($typeName)) {
                        return $typeName::cases()[0];
                    } elseif (class_exists($typeName)) {
                        return \Phake::mock($typeName);
                    } elseif ($returnType->allowsNull()) {
                        return null;
                    } elseif (interface_exists($typeName)) {
                        return \Phake::mock($typeName);
                    }
            }
        } elseif ($returnType instanceof \ReflectionIntersectionType) {
            /** @psalm-suppress ArgumentTypeCoercion */
            return \Phake::mock(array_map(static fn (\ReflectionNamedType $t): string => $t->getName(), $returnType->getTypes()));
        } elseif ($returnType instanceof \ReflectionUnionType) {
            foreach ($returnType->getTypes() as $type) {
                return $this->getReturnTypeResult($type, $class);
            }
        }

        throw new \Exception('Unable to create a smart answer for type \'' . (string) $returnType . '\'');
    }

    public function getAnswerCallback(mixed $context, string $name): callable
    {
        $class = new \ReflectionClass($context);
        if ($class->hasMethod($name)) {
            $name = $class->getMethod($name);
            $defaultAnswer = $this->getReturnTypeResult($name->getReturnType(), $name->getDeclaringClass());

        } else {
            $property = $class->getProperty($name);
            if ($property->hasDefaultValue()) {
                $defaultAnswer = $property->getDefaultValue();
            } else {
                $defaultAnswer = $this->getReturnTypeResult($property->getType(), $property->getDeclaringClass());
            }
        }
        return static fn (mixed ...$args): mixed => $defaultAnswer;
    }
}
