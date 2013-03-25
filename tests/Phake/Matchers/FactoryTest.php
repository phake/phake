<?php
/* 
 * Phake - Mocking Framework
 * 
 * Copyright (c) 2010-2012, Mike Lively <m@digitalsandwich.com>
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

class Phake_Matchers_FactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Phake_Matchers_Factory
     */
    private $factory;

    /**
     * Sets up the test fixture
     */
    public function setUp()
    {
        $this->factory = new Phake_Matchers_Factory();
    }

    /**
     * Tests creating a default matcher
     */
    public function testDefaultMatcher()
    {
        $matcher = $this->factory->createMatcher('foo');

        $this->assertInstanceOf('Phake_Matchers_EqualsMatcher', $matcher);

        $value = 'foo';
        $this->assertTrue($matcher->matches($value));
    }

    /**
     * Tests creating a pass through matcher
     */
    public function testPassThroughMatcher()
    {
        $matcher = $this->getMock('Phake_Matchers_IArgumentMatcher');

        $retMatcher = $this->factory->createMatcher($matcher);

        $this->assertSame($matcher, $retMatcher);
    }

    /**
     * Tests creating a phpunit adapter matcher
     */
    public function testPHPUnitConstraint()
    {
        $matcher = $this->getMock('PHPUnit_Framework_Constraint');
        $matcher->expects($this->once())
            ->method('evaluate')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(true));

        $retMatcher = $this->factory->createMatcher($matcher);

        $value = 'foo';
        $this->assertTrue($retMatcher->matches($value));
    }

    /**
     * Tests creating a hamcrest adapter matcher
     */
    public function testHamcrestMatcher()
    {
        $matcher = $this->getMock('Hamcrest_Matcher');
        $matcher->expects($this->once())
            ->method('matches')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue(true));

        $retMatcher = $this->factory->createMatcher($matcher);

        $value = 'foo';
        $this->assertTrue($retMatcher->matches($value));
    }

    /**
     * Tests the factorying of multiple matchers
     */
    public function testCreatingMultipleMatchers()
    {
        $arguments = array(
            'foo',
            $argMatcher = $this->getMock('Phake_Matchers_IArgumentMatcher'),
            $this->getMock('PHPUnit_Framework_Constraint'),
        );

        $matchers = $this->factory->createMatcherArray($arguments);

        $this->assertInstanceOf('Phake_Matchers_EqualsMatcher', $matchers[0]);
        $this->assertSame($argMatcher, $matchers[1]);
        $this->assertInstanceOf('Phake_Matchers_PHPUnitConstraintAdapter', $matchers[2]);
    }
}


