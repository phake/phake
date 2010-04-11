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

require_once 'Phake/Proxies/VerifierProxy.php';
require_once 'Phake/CallRecorder/Verifier.php';

/**
 * Description of VerifierProxyTest
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class Phake_Proxies_VerifierProxyTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var Phake_CallRecorder_Verifier
	 */
	private $verifier;

	/**
	 * @var Phake_Proxies_VerifierProxy
	 */
	private $proxy;

	public function setUp()
	{
		$this->verifier = $this->getMock('Phake_CallRecorder_Verifier', array(), array(), '', FALSE);

		$this->proxy = new Phake_Proxies_VerifierProxy($this->verifier);
	}

	/**
	 * Tests that the proxy will call the verifier with the method properly forwarded
	 */
	public function testVerifierCallsAreForwardedMethod()
	{
		$this->verifier->expects($this->once())
			->method('verifyCall')
			->with($this->equalTo('foo'))
			->will($this->returnValue(TRUE));

		$this->proxy->foo();
	}

	/**
	 * Tests that exceptions are thrown when verifier calls fail
	 * @expectedException Exception
	 */
	public function testVerifierCallsThrowExceptionWhenNotFound()
	{
		$this->verifier->expects($this->any())
			->method('verifyCall')
			->will($this->returnValue(FALSE));

		$this->proxy->foo();
	}

	/**
	 * Tests that verifier calls will forward method arguments properly
	 */
	public function testVerifierCallsAreForwardedArguments()
	{
		$argumentMatcher = $this->getMock('Phake_Matchers_EqualsMatcher', array(), array(), '', FALSE);

		$this->verifier->expects($this->once())
			->method('verifyCall')
			->with($this->anything(), $this->equalTo(array($argumentMatcher)))
			->will($this->returnValue(TRUE));

		$this->proxy->foo($argumentMatcher);
	}

	/**
	 * Tests that verifier calls that are not given an argument matcher will generate an equals matcher
	 * with the given value.
	 */
	public function testProxyTransformsNonMatchersToEqualsMatcher()
	{
		$argumentMatcher = new Phake_Matchers_EqualsMatcher('test');

		$this->verifier->expects($this->once())
			->method('verifyCall')
			->with($this->anything(), $this->equalTo(array($argumentMatcher)))
			->will($this->returnValue(TRUE));

		$this->proxy->foo('test');
	}
}
?>
