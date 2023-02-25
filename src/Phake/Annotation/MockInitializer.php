<?php

declare(strict_types=1);

namespace Phake\Annotation;

/*
 * Phake - Mocking Framework
 *
 * Copyright (c) 2010-2022, Mike Lively <mike.lively@sellingsource.com>
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
 * @author     Pierrick Charron <pierrick@adoy.net>
 * @copyright  2010 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.digitalsandwich.com/
 */

/**
 * Initializes all properties of a given object that have the @Mock annotation.
 *
 * The class can be passed to the Mock annotation or it can also read the standard @var -annotation.
 *
 * In either case the fully qualified class name should be used. The use statements are not observed.
 */
class MockInitializer
{
    /**
     * @var IReader|null
     */
    private static ?IReader $defaultReader = null;

    /**
     * @var IReader
     */
    private IReader $reader;

    public static function getDefaultReader(): IReader
    {
        if (!isset(self::$defaultReader)) {
            self::$defaultReader = PHP_VERSION_ID < 80000 ? new LegacyReader() : new NativeReader();
        }

        return self::$defaultReader;
    }

    /**
     * @return void
     */
    public static function setDefaultReader(IReader $reader): void
    {
        self::$defaultReader = $reader;
    }

    public function __construct(IReader $reader = null)
    {
        $this->reader = $reader ?: self::getDefaultReader();
    }

    /**
     * @param object $object
     * @return void
     */
    public function initialize(object $object): void
    {
        $class = new \ReflectionClass($object);

        foreach ($this->reader->getPropertiesWithMockAnnotation($class) as $property) {
            $mockedClass = $this->reader->getMockType($property);
            if (!$mockedClass && method_exists($property, 'hasType') && $property->hasType()) {
                $type = $property->getType();
                if ($type instanceof \ReflectionNamedType) {
                    $mockedClass = $type->getName();
                }
            }

            if ($mockedClass) {
                $property->setAccessible(true);
                $property->setValue($object, \Phake::mock($mockedClass));
            }
        }
    }
}
