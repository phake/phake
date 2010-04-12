<?php
/* 
 * Phake - Mocking Framework
 * 
 * Copyright (c) 2010, Mike Lively <mike.lively@sellingsource.com>
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

require_once 'Phake/Matchers/MethodMatcher.php';
require_once 'Phake/Matchers/IArgumentMatcher.php';

class PHake_Matchers_MethodMatcherTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var Phake_Matchers_MethodMatcher
	 */
	private $matcher;

	/**
	 * @var array
	 */
	private $arguments;

	public function setUp()
	{
		$this->arguments = array($this->getMock('Phake_Matchers_IArgumentMatcher'), $this->getMock('Phake_Matchers_IArgumentMatcher'));
		$this->matcher = new Phake_Matchers_MethodMatcher('foo', $this->arguments);
	}

	/**
	 * Tests that passing invalid arguments to the Method Matcher will cause an exception
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidParametersThrow()
	{
		new Phake_Matchers_MethodMatcher('foo', array('blah'));
	}

	/**
	 * Tests that the method matcher will forward arguments on.
	 */
	public function testMatchesForwardsParameters()
	{
		$arguments = array($this->getMock('Phake_Matchers_IArgumentMatcher'));
		$matcher = new Phake_Matchers_MethodMatcher('foo', $arguments);
		$arguments[0]->expects($this->any())
						->method('matches')
						->with($this->equalTo('foo'))
						->will($this->returnValue(TRUE));
		
		$matcher->matches('foo', array('foo'));
	}

	/**
	 * Tests that the method matcher will return true when all is well.
	 */
	public function testMatchesSuccessfullyMatches()
	{
		$this->arguments[0]->expects($this->any())
						->method('matches')
						->will($this->returnValue(TRUE));

		$this->arguments[1]->expects($this->any())
						->method('matches')
						->will($this->returnValue(TRUE));

		$this->assertTrue($this->matcher->matches('foo', array('foo', 'bar')));
	}

	/**
	 * Tests that the matcher will return false on mismatched method name.
	 */
	public function testNoMatcherOnBadMethod()
	{
		$this->arguments[0]->expects($this->any())
						->method('matches')
						->will($this->returnValue(TRUE));

		$this->arguments[1]->expects($this->any())
						->method('matches')
						->will($this->returnValue(TRUE));

		$this->assertFalse($this->matcher->matches('test', array('foo', 'bar')));
	}

	/**
	 * Tests that the matcher will return false on mismatched argument 1.
	 */
	public function testNoMatcherOnBadArg1()
	{
		$this->arguments[0]->expects($this->any())
						->method('matches')
						->will($this->returnValue(FALSE));

		$this->arguments[1]->expects($this->any())
						->method('matches')
						->will($this->returnValue(TRUE));

		$this->assertFalse($this->matcher->matches('foo', array('foo', 'bar')));
	}

	/**
	 * Tests that the matcher will return false on mismatched argument 2.
	 */
	public function testNoMatcherOnBadArg2()
	{
		$this->arguments[0]->expects($this->any())
						->method('matches')
						->will($this->returnValue(TRUE));

		$this->arguments[1]->expects($this->any())
						->method('matches')
						->will($this->returnValue(FALSE));

		$this->assertFalse($this->matcher->matches('foo', array('foo', 'bar')));
	}
}

?>
