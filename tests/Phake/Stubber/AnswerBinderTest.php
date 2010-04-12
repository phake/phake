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

require_once 'Phake/Stubber/AnswerBinder.php';
require_once 'Phake/Stubber/IStubbable.php';
require_once 'Phake/Stubber/StaticAnswer.php';
require_once 'Phake/Matchers/MethodMatcher.php';

/**
 * Tests the Answer Factory
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class Phake_Stubber_AnswerBinderTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var Phake_Stubber_AnswerBinder
	 */
	private $binder;

	/**
	 * @var Phake_Stubber_IStubbable
	 */
	private $mock;

	/**
	 * @var Phake_Matchers_MethodMatcher
	 */
	private $matcher;

	/**
	 * Sets up the test fixture
	 */
	public function setUp()
	{
		$this->mock = $this->getMock('Phake_Stubber_IStubbable');
		$this->matcher = $this->getMock('Phake_Matchers_MethodMatcher', array(), array(), '', FALSE);
		$this->binder = new Phake_Stubber_AnswerBinder($this->mock, $this->matcher);
	}

	public function testBindAnswer()
	{
		$answer = $this->getMock('Phake_Stubber_StaticAnswer', array(), array(), '', FALSE);
		$this->mock->expects($this->once())
			->method('__PHAKE_addAnswer')
			->with($this->equalTo($answer), $this->equalTo($this->matcher));
		
		$this->binder ->bindAnswer($answer);
	}
}
?>
