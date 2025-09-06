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

use PHPUnit\Framework\TestCase;

/**
 * Tests the behavior of the Phake class.
 *
 * The tests below are really all integration tests.
 *
 * @author Pierrick Charron <pierrick@adoy.net>
 */
class PhakePropertyHookTest extends TestCase
{
    public function setUp(): void
    {
        if (PHP_VERSION_ID < 80400) {
            $this->markTestSkipped('never type is not supported in PHP versions prior to 8.4');
        }
        Phake::setClient(Phake::CLIENT_PHPUNIT);
    }

    protected function tearDown(): void
    {
        Phake::resetStaticInfo();
        Phake::setClient(Phake::CLIENT_PHPUNIT);
    }

    public function testCanMockPropertyHooks(): void
    {
        $mock = Phake::mock(PhakeTest\PropertyHooks::class);
        $this->assertInstanceOf(PhakeTest\PropertyHooks::class, $mock);
        $this->assertInstanceOf(Phake\IMock::class, $mock);
    }

    public function testCanMockPropertyHooksOnInterface(): void
    {
        $mock = Phake::mock(PhakeTest\PropertyHooksInterface::class);
        $this->assertInstanceOf(PhakeTest\PropertyHooksInterface::class, $mock);
        $this->assertInstanceOf(Phake\IMock::class, $mock);
    }

    public function testPropertyHooksGetter(): void
    {
        $mock = Phake::mock(PhakeTest\PropertyHooks::class);
        Phake::when($mock)->publicPropWithHooks->get()->thenReturn('myValue1');
        $this->assertSame('myValue1', $mock->publicPropWithHooks);

        $mock = Phake::mock(PhakeTest\PropertyHooks::class);
        Phake::when($mock)->publicPropWithHooks->get()->thenReturnCallback(fn() => 'callBackReturn1');
        $this->assertSame('callBackReturn1', $mock->publicPropWithHooks);

        $mock = Phake::mock(PhakeTest\PropertyHooks::class);
        Phake::when($mock)->publicPropWithHooks->get()->thenCallParent();
        $this->assertSame('foobar', $mock->publicPropWithHooks);

        $mock = Phake::mock(PhakeTest\PropertyHooks::class);
        Phake::when($mock)->publicPropWithHooks->get()->thenThrow($expectedException = new \RuntimeException());
        $this->assertSameException($expectedException, fn() => $mock->publicPropWithHooks);
    }

    public function testPropertyHooksGetterChaining(): void
    {
        $mock = Phake::mock(PhakeTest\PropertyHooks::class);
        Phake::when($mock)->publicPropWithHooks->get()
            ->thenReturn('myValue1')
            ->thenReturn('myValue2')
            ->thenReturnCallback(fn() => 'callBackReturn1')
            ->thenCallParent()
            ->thenThrow($expectedException = new \RuntimeException());

        $this->assertSame('myValue1', $mock->publicPropWithHooks);
        $this->assertSame('myValue2', $mock->publicPropWithHooks);
        $this->assertSame('callBackReturn1', $mock->publicPropWithHooks);
        $this->assertSame('foobar', $mock->publicPropWithHooks);
        $this->assertSameException($expectedException, fn() => $mock->publicPropWithHooks);
    }

    public function testPropertyHooksGetterShorthand(): void
    {
        $mock = Phake::mock(PhakeTest\PropertyHooks::class);
        Phake::when($mock)->publicPropWithHooks->thenReturn('myValue1');
        $this->assertSame('myValue1', $mock->publicPropWithHooks);

        $mock = Phake::mock(PhakeTest\PropertyHooks::class);
        Phake::when($mock)->publicPropWithHooks->thenReturnCallback(fn() => 'callBackReturn1');
        $this->assertSame('callBackReturn1', $mock->publicPropWithHooks);

        $mock = Phake::mock(PhakeTest\PropertyHooks::class);
        Phake::when($mock)->publicPropWithHooks->thenCallParent();
        $this->assertSame('foobar', $mock->publicPropWithHooks);

        $mock = Phake::mock(PhakeTest\PropertyHooks::class);
        Phake::when($mock)->publicPropWithHooks->thenThrow($expectedException = new \RuntimeException());
        $this->assertSameException($expectedException, fn() => $mock->publicPropWithHooks);
    }

    public function testPropertyHooksGetterShorthandChaining(): void
    {
        $mock = Phake::mock(PhakeTest\PropertyHooks::class);
        Phake::when($mock)->publicPropWithHooks
            ->thenReturn('myValue1')
            ->thenReturn('myValue2')
            ->thenReturnCallback(fn() => 'callBackReturn1')
            ->thenCallParent()
            ->thenThrow($expectedException = new \RuntimeException());

        $this->assertSame('myValue1', $mock->publicPropWithHooks);
        $this->assertSame('myValue2', $mock->publicPropWithHooks);
        $this->assertSame('callBackReturn1', $mock->publicPropWithHooks);
        $this->assertSame('foobar', $mock->publicPropWithHooks);
        $this->assertSameException($expectedException, fn() => $mock->publicPropWithHooks);
    }

    public function testPropertyHooksSetter(): void
    {
        $mock = Phake::mock(PhakeTest\PropertyHooks::class);
        Phake::when($mock)->publicPropWithHooks->get()->thenCallParent();

        Phake::when($mock)->publicPropWithHooks->set("setValue2")->thenCallParent();
        Phake::when($mock)->publicPropWithHooks->set("setValue3")->thenThrow($expectedException = new \RuntimeException());

        $mock->publicPropWithHooks = 'setValue1';
        $this->assertSame('foobar', $mock->publicPropWithHooks);

        $mock->publicPropWithHooks = 'setValue2';
        $this->assertSame('setValue2', $mock->publicPropWithHooks);

        $this->assertSameException($expectedException, fn() => $mock->publicPropWithHooks = 'setValue3');
    }

    public function testPropertyHooksSetterChaining(): void
    {
        $mock = Phake::mock(PhakeTest\PropertyHooks::class);
        Phake::when($mock)->publicPropWithHooks->get()->thenCallParent();

        Phake::when($mock)->publicPropWithHooks->set(Phake::anyParameters())
            ->thenCallParent()
            ->thenThrow($expectedException = new \RuntimeException());

        $mock->publicPropWithHooks = 'setValue1';
        $this->assertSame('setValue1', $mock->publicPropWithHooks);

        $this->assertSameException($expectedException, fn() => $mock->publicPropWithHooks = 'setValue2');
    }

    public function testVerifyNoFurtherInteractionWithPropertyHooks(): void
    {
        $this->expectException(Phake\Exception\VerificationException::class);

        $mock = Phake::mock(PhakeTest\PropertyHooks::class);

        Phake::verifyNoFurtherInteraction($mock);
        Phake::setClient(Phake::CLIENT_DEFAULT);

        $mock->publicPropWithHooks = 'setValue1';
    }

    public function testVerifySetWithPropertyHooks(): void
    {
        $mock = Phake::mock(PhakeTest\PropertyHooks::class);

        $mock->publicPropWithHooks = 'setValue1';
        $mock->publicPropWithHooks;
        $mock->publicPropWithHooks = 'setValue2';

        Phake::verify($mock, Phake::times(2))->publicPropWithHooks->set(Phake::anyParameters());
        Phake::verify($mock)->publicPropWithHooks->set('setValue1');
        Phake::verify($mock)->publicPropWithHooks->get();
        Phake::verify($mock)->publicPropWithHooks->set('setValue2');
        Phake::verify($mock, Phake::never())->publicPropWithHooks->set('setValue3');
    }

    public function testVerifyFail(): void
    {
        $this->expectException(Phake\Exception\VerificationException::class);
        Phake::setClient(Phake::CLIENT_DEFAULT);

        $mock = Phake::mock(PhakeTest\PropertyHooks::class);

        $mock->publicPropWithHooks = 'setValue1';

        Phake::verify($mock)->publicPropWithHooks->set('setValue2');
    }

    public function testVerifyInOrder(): void
    {
        $mock = Phake::mock(PhakeTest\PropertyHooks::class);

        $mock->publicPropWithHooks = 'setValue1';
        $mock->publicPropWithHooks;
        $mock->publicPropWithHooks = 'setValue2';

        Phake::verify($mock, Phake::times(2))->publicPropWithHooks->set(Phake::anyParameters());
        Phake::inOrder(
            Phake::verify($mock)->publicPropWithHooks->set('setValue1'),
            Phake::verify($mock)->publicPropWithHooks->get(),
            Phake::verify($mock)->publicPropWithHooks->set('setValue2')
        );
    }

    public function testVerifyInOrderFailure(): void
    {
        $this->expectException(Phake\Exception\VerificationException::class);
        Phake::setClient(Phake::CLIENT_DEFAULT);

        $mock = Phake::mock(PhakeTest\PropertyHooks::class);

        $mock->publicPropWithHooks = 'setValue1';
        $mock->publicPropWithHooks = 'setValue2';

        Phake::verify($mock, Phake::times(2))->publicPropWithHooks->set(Phake::anyParameters());
        Phake::inOrder(
            Phake::verify($mock)->publicPropWithHooks->set('setValue2'),
            Phake::verify($mock)->publicPropWithHooks->set('setValue1')
        );
    }

    public function testVerifyWithCaptureAll(): void
    {
        $mock = Phake::mock(PhakeTest\PropertyHooks::class);

        $mock->publicPropWithHooks = 'setValue1';
        $mock->publicPropWithHooks = 'setValue2';

        Phake::verify($mock, Phake::times(2))->publicPropWithHooks->set(Phake::captureAll($args));
        $this->assertSame(['setValue1', 'setValue2'], $args);
    }

    public function testVerifyWithCaptureAllWhen(): void
    {
        $mock = Phake::mock(PhakeTest\PropertyHooks::class);

        $mock->publicPropWithHooks = 'setValue1';
        $mock->publicPropWithHooks = 'setValue2';
        $mock->publicPropWithHooks = 'setValue1';

        Phake::verify($mock, Phake::times(2))->publicPropWithHooks->set(Phake::captureAll($args)->when('setValue1'));
        $this->assertSame(['setValue1', 'setValue1'], $args);
    }

    public function testVerifyWithCapture(): void
    {
        $mock = Phake::mock(PhakeTest\PropertyHooks::class);

        $mock->publicPropWithHooks = 'setValue1';

        Phake::verify($mock)->publicPropWithHooks->set(Phake::capture($arg));
        $this->assertSame('setValue1', $arg);
    }

    public function testVerifyWithCaptureWhen(): void
    {
        $mock = Phake::mock(PhakeTest\PropertyHooks::class);

        $mock->publicPropWithHooks = 'setValue1';
        $mock->publicPropWithHooks = 'setValue2';

        Phake::verify($mock)->publicPropWithHooks->set(Phake::capture($arg)->when('setValue1'));
        $this->assertSame('setValue1', $arg);
    }

    private function assertSameException(\Throwable $expected, callable $func): void
    {
        try {
            $exception = null;
            $func();
        } catch (\Throwable $e) {
            $exception = $e;
        } finally {
            $this->assertSame($expected, $e, 'Failed asserting that the exception thrown is the same as the expected one.');
        }
    }
}
