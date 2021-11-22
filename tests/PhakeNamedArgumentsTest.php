<?php

if (PHP_VERSION_ID >= 80000) {
    $fp = fopen(__FILE__, 'r');
    fseek($fp, __COMPILER_HALT_OFFSET__);
    eval(stream_get_contents($fp));
}

__halt_compiler();

/*
 * Phake - Mocking Framework
 *
 * Copyright (c) 2010-2021, Mike Lively <mike.lively@sellingsource.com>
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

use PHPUnit\Framework\TestCase;

/**
 * Tests the behavior of the Phake class.
 *
 * The tests below are really all integration tests for named arguments only
 *
 * @author Pierrick Charron <pierrick@adoy.net>
 */
class PhakeNamedArgumentsTest extends TestCase
{
    public function testNamedArgumentsInWhen()
    {
        $mock = Phake::mock('PhakeTest_MockedClass');
        Phake::when($mock)->fooWithLotsOfParameters(parm3: 3, parm2: 2, parm1: 1)->thenReturn(42);

        $this->assertSame(42, $mock->fooWithLotsOfParameters(1, 2, 3));
        
        Phake::verify($mock)->fooWithLotsOfParameters(parm3: 3, parm2: 2, parm1: 1);
    }

    public function testNamedArgumentsInCall()
    {
        $mock = Phake::mock('PhakeTest_MockedClass');
        Phake::when($mock)->fooWithLotsOfParameters(1, 2, 3)->thenReturn(42);

        $this->assertSame(42, $mock->fooWithLotsOfParameters(parm3: 3, parm2: 2, parm1: 1));

        Phake::verify($mock)->fooWithLotsOfParameters(1, 2, 3);
    }

    public function testNamedArgumentsInBoth()
    {
        $mock = Phake::mock('PhakeTest_MockedClass');
        Phake::when($mock)->fooWithLotsOfParameters(parm3: 3, parm2: 2, parm1: 1)->thenReturn(42);

        $this->assertSame(42, $mock->fooWithLotsOfParameters(parm3: 3, parm2: 2, parm1: 1));

        Phake::verify($mock)->fooWithLotsOfParameters(parm3: 3, parm2: 2, parm1: 1);
    }

    public function testMixedNamedArguments()
    {
        $mock = Phake::mock('PhakeTest_MockedClass');
        Phake::when($mock)->fooWithLotsOfParameters(1, parm3: 3, parm2: 2)->thenReturn(42);

        $this->assertSame(42, $mock->fooWithLotsOfParameters(1, 2, 3));

        Phake::verify($mock)->fooWithLotsOfParameters(1, parm3: 3, parm2: 2);
    }

    public function testNamedArgumentsWithNullValue()
    {
        $mock = Phake::mock('PhakeTest_MockedClass');
        Phake::when($mock)->fooWithLotsOfParameters(parm3: 3, parm2: null, parm1: 1)->thenReturn(42);

        $this->assertSame(42, $mock->fooWithLotsOfParameters(parm3: 3, parm2: null, parm1: 1));

        Phake::verify($mock)->fooWithLotsOfParameters(parm3: 3, parm2: null, parm1: 1);
    }

    public function testNamedArgumentsWithDefault()
    {
        $mock = Phake::mock('PhakeTest_MockedClass');

        Phake::when($mock)->fooWithMultipleDefault(p2: 2)->thenReturn(42);

        $this->assertSame(42, $mock->fooWithMultipleDefault(p2: 2));

        Phake::verify($mock)->fooWithMultipleDefault(p2: 2);
    }
   
    public function testVariadicWithNamedParams()
    {
        $mock = Phake::mock('PhakeTest_Variadic');
        Phake::when($mock)->variadicMethod(1, 2, c: 3, d: 4)->thenReturn(42);

        $this->assertSame(42, $mock->variadicMethod(d: 4, c: 3, b: 2, a: 1));

        Phake::verify($mock)->variadicMethod(1, 2, c: 3, d: 4);
    }

    public function testVariadicWithMixedNamedArguments()
    {
        $mock = Phake::mock('PhakeTest_Variadic');
        Phake::when($mock)->variadicMethod(1, 2, 3, 4, e: 5, f: 6)->thenReturn(42);

        $this->assertSame(42, $mock->variadicMethod(1, 2, 3, 4, e: 5, f: 6));

        Phake::verify($mock)->variadicMethod(1, 2, 3, 4, e: 5, f: 6);
    }

    public function testVariadicWithNamedParametersShuffled()
    {
        $mock = Phake::mock('PhakeTest_Variadic');
        Phake::when($mock)->variadicMethod(b: 2, a: 1, c: 3, d: 4)->thenReturn(42);

        $this->assertSame(42, $mock->variadicMethod(d: 4, c: 3, b: 2, a: 1));

        Phake::verify($mock)->variadicMethod(c: 3, d: 4, a: 1, b: 2);
    }

    public function testWithNamedParanetersAndEmptyVariadic()
    {
        $mock = Phake::mock('PhakeTest_Variadic');
        Phake::when($mock)->variadicMethod(a: 'a', b: 'b')->thenReturn(42);

        $this->assertSame(42, $mock->variadicMethod('a', b: 'b'));

        Phake::verify($mock)->variadicMethod('a', b: 'b');
    }
}
