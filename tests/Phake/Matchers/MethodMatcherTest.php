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

namespace Phake\Matchers;

use Phake;
use PHPUnit\Framework\TestCase;

class MethodMatcherTest extends TestCase
{
    private MethodMatcher $matcher;

    /**
     * @Mock
     */
    private IChainableArgumentMatcher $rootArgumentMatcher;

    private array $arguments;

    public function setUp(): void
    {
        Phake::initAnnotations($this);

        $this->matcher   = new MethodMatcher('foo', $this->rootArgumentMatcher);
    }

    /**
     * Tests that the method matcher will forward arguments on.
     */
    public function testMatchesForwardsParameters(): void
    {
        $arguments = ['foo', 'bar'];
        $this->matcher->matches('foo', $arguments);

        Phake::verify($this->rootArgumentMatcher)->doArgumentsMatch(['foo', 'bar']);
    }

    /**
     * Tests that the method matcher will return true when all is well.
     */
    public function testMatchesSuccessfullyMatches(): void
    {
        Phake::when($this->rootArgumentMatcher)->doArgumentsMatch(Phake::anyParameters())->thenReturn(true);

        $arguments = ['foo', 'bar'];
        $this->assertTrue($this->matcher->matches('foo', $arguments));
    }

    /**
     * Tests that the matcher will return false on mismatched method name.
     */
    public function testNoMatcherOnBadMethod(): void
    {
        Phake::when($this->rootArgumentMatcher)->doArgumentsMatch(Phake::anyParameters())->thenReturn(true);

        $arguments = ['foo', 'bar'];
        $this->assertFalse($this->matcher->matches('test', $arguments));
    }

    /**
     * Tests that the matcher will return false on mismatched argument 1.
     */
    public function testNoMatcherOnBadArg1(): void
    {
        Phake::when($this->rootArgumentMatcher)->doArgumentsMatch(Phake::anyParameters())->thenThrow(new Phake\Exception\MethodMatcherException());

        $arguments = ['foo', 'bar'];
        $this->assertFalse($this->matcher->matches('foo', $arguments));
    }

    public function testAnyParameterMatching(): void
    {
        $matcher = new MethodMatcher('method', new AnyParameters());

        $arguments = [1, 2, 3];
        $this->assertTrue($matcher->matches('method', $arguments));
        $arguments = [2, 3, 4];
        $this->assertTrue($matcher->matches('method', $arguments));
        $arguments = [3, 4, 5];
        $this->assertTrue($matcher->matches('method', $arguments));
    }

    public function testSetterMatcher(): void
    {
        $matcher = new MethodMatcher('method', new ReferenceSetter(42));

        $value        = 'blah';
        $arguments    = [];
        $arguments[0] =&$value;

        $matcher->matches('method', $arguments);

        $this->assertEquals(42, $value);
    }

    public function testNullMatcherWithNoArguments(): void
    {
        $matcher = new MethodMatcher('method', null);

        $emptyArray = [];
        $this->assertTrue($matcher->matches('method', $emptyArray));
    }

    public function testNullMatcherWithArguments(): void
    {
        $matcher = new MethodMatcher('method', null);

        $arguments = ['foo'];
        $this->assertFalse($matcher->matches('method', $arguments));
    }
}
