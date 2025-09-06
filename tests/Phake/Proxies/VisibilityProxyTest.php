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

namespace Phake\Proxies;

use Phake;
use PHPUnit\Framework\TestCase;

/**
 * @author Mike Lively <m@digitalsandwich.com>
 */
class VisibilityProxyTest extends TestCase
{
    public function testCallingNonExistantMethod(): void
    {
        $test = new \PhakeTest\MockedClass();
        $proxy = new VisibilityProxy($test);

        $this->expectException(\InvalidArgumentException::class);
        $proxy->badFunctionName();
    }

    public function testCallingMagicMethod(): void
    {
        $mock = Phake::mock(\PhakeTest\MagicClass::class);
        $proxy = new VisibilityProxy($mock);

        Phake::when($mock)->test()->thenReturn('bar');

        $this->assertEquals('bar', $proxy->test());

        Phake::verify($mock)->test();
    }

    public function testCallingPrivateMethod(): void
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped("Can't call private methods with hhvm");
        }
        $mock = Phake::mock(\PhakeTest\MockedClass::class);
        $proxy = new VisibilityProxy($mock);

        $this->assertEquals('blah', $proxy->privateFunc());
    }

    public function testCallingProtectedMethod(): void
    {
        $mock = Phake::mock(\PhakeTest\MockedClass::class);
        $proxy = new VisibilityProxy($mock);

        Phake::when($mock)->innerFunc()->thenReturn('bar');

        $this->assertEquals('bar', $proxy->innerFunc());

        Phake::verify($mock)->innerFunc();
    }

    public function testCallingPublicMethod(): void
    {
        $mock = Phake::mock(\PhakeTest\MockedClass::class);
        $proxy = new VisibilityProxy($mock);

        Phake::when($mock)->foo()->thenReturn('bar');

        $this->assertEquals('bar', $proxy->foo());

        Phake::verify($mock)->foo();
    }
}
