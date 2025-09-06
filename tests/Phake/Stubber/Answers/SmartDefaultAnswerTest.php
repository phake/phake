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

use PhakeTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the functionality of the parent delegate
 */
class SmartDefaultAnswerTest extends TestCase
{
    private SmartDefaultAnswer $answer;

    /**
     * Sets up the test fixture
     */
    public function setUp(): void
    {
        $this->answer = new SmartDefaultAnswer();
    }

    public static function typeReturnMap(): iterable
    {
        yield 'int' => ['intReturn', 0];
        yield 'float' => ['floatReturn', 0.0];
        yield 'string' => ['stringReturn', ''];
        yield 'boolean' => ['boolReturn', false];
        yield 'array' => ['arrayReturn', []];
    }

    /**
     * @dataProvider typeReturnMap
     */
    #[DataProvider('typeReturnMap')]
    public function testSimpleReturn($method, $expectedValue): void
    {
        $context = new PhakeTest\ScalarTypes();
        $cb = $this->answer->getAnswerCallback($context, $method);

        $this->assertSame($expectedValue, $cb());
    }

    public function testCallableReturn(): void
    {
        $context = new PhakeTest\ScalarTypes();
        $cb = $this->answer->getAnswerCallback($context, 'callableReturn');

        $this->assertIsCallable($cb());
    }

    public function testObjectReturn(): void
    {
        $context = new PhakeTest\ScalarTypes();
        $cb = $this->answer->getAnswerCallback($context, 'objectReturn');

        $this->assertInstanceOf(PhakeTest\A::class, $cb());
        $this->assertInstanceOf(\Phake\IMock::class, $cb());
    }

    public function testNullableInterfaceReturn(): void
    {
        $context = new PhakeTest\NullableTypes();
        $cb = $this->answer->getAnswerCallback($context, 'interfaceReturn');

        $this->assertNull($cb());
    }

    public function testInterfaceReturn(): void
    {
        $context = new PhakeTest\ScalarTypes();
        $cb = $this->answer->getAnswerCallback($context, 'interfaceReturn');

        $this->assertInstanceOf('PhakeTest\MockedInterface', $cb());
        $this->assertInstanceOf(\Phake\IMock::class, $cb());
    }

    public function testSelfReturn(): void
    {
        $context = new PhakeTest\ScalarTypes();
        $cb = $this->answer->getAnswerCallback($context, 'selfReturn');

        $this->assertInstanceOf(PhakeTest\ScalarTypes::class, $cb());
    }

    public function testUnionTypeReturn(): void
    {
        $context = new PhakeTest\UnionTypes();
        $cb = $this->answer->getAnswerCallback($context, 'unionReturn');

        $this->assertTrue(in_array($cb(), [0, '']));
    }

    public function testIntersectionTypeReturn(): void
    {
        if (PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('Intersection types are not supported in PHP versions prior to 8.1');
        }

        $context = new PhakeTest\IntersectionTypes();
        $cb = $this->answer->getAnswerCallback($context, 'intersectionReturn');

        $result = $cb();
        $this->assertInstanceOf(\ArrayAccess::class, $result);
        $this->assertInstanceOf(\Countable::class, $result);
    }

    public function testEnumReturnType(): void
    {
        if (PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('Intersection types are not supported in PHP versions prior to 8.1');
        }

        $context = new PhakeTest\EnumType();
        $cb = $this->answer->getAnswerCallback($context, 'enumReturn');

        $result = $cb();
        $this->assertInstanceOf(PhakeTest\SomeEnum::class, $result);

    }

    public function testDNFTypeReturn(): void
    {
        if (PHP_VERSION_ID < 80200) {
            $this->markTestSkipped('DNF types are not supported in PHP versions prior to 8.2');
        }

        $context = new PhakeTest\DNFTypes();
        $cb = $this->answer->getAnswerCallback($context, 'dnfReturn');

        $result = $cb();
        $this->assertInstanceOf(\Traversable::class, $result);
        $this->assertInstanceOf(\Countable::class, $result);
    }

    public function testReturnTrue(): void
    {
        if (PHP_VERSION_ID < 80200) {
            $this->markTestSkipped('true return type is not supported in PHP versions prior to 8.2');
        }

        $context = new PhakeTest\TrueType();
        $cb = $this->answer->getAnswerCallback($context, 'trueReturn');

        $result = $cb();
        $this->assertTrue($result);
    }

    public function testReturnFalse(): void
    {
        if (PHP_VERSION_ID < 80200) {
            $this->markTestSkipped('false return type is not supported in PHP versions prior to 8.2');
        }

        $context = new PhakeTest\FalseType();
        $cb = $this->answer->getAnswerCallback($context, 'falseReturn');

        $result = $cb();
        $this->assertFalse($result);
    }

    public function testReturnNull(): void
    {
        if (PHP_VERSION_ID < 80200) {
            $this->markTestSkipped('null return type is not supported in PHP versions prior to 8.2');
        }

        $context = new PhakeTest\NullType();
        $cb = $this->answer->getAnswerCallback($context, 'nullReturn');

        $result = $cb();
        $this->assertNull($result);
    }
}
