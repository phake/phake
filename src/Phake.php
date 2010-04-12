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

require_once 'Phake/Facade.php';
require_once 'Phake/ClassGenerator/MockClass.php';
require_once 'Phake/CallRecorder/Recorder.php';
require_once 'Phake/Proxies/VerifierProxy.php';
require_once 'Phake/Proxies/StubberProxy.php';
require_once 'Phake/Matchers/EqualsMatcher.php';
require_once 'Phake/Matchers/Factory.php';

/**
 * Phake - PHP Test Doubles Framework
 *
 * Phake provides the functionality required for create mocks, stubs and spies. This is to allow
 * a developer to isolate the code in a system under test (SUT) to provide better control of what
 * code is being exercised in a particular test.
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class Phake
{
	/**
	 * @var Phake_Facade
	 */
	private static $phake;

	/**
	 * Returns a new mock object based on the given class name.
	 * @param string $className
	 * @return mixed
	 */
	public static function mock($className)
	{
		return self::getPhake()->mock($className, new Phake_ClassGenerator_MockClass(), new Phake_CallRecorder_Recorder());
	}

	/**
	 * Creates a new verifier for the given mock object.
	 * @param Phake_CallRecorder_ICallRecorderContainer $mock
	 * @return Phake_CallRecorder_VerifierProxy
	 */
	public static function verify(Phake_CallRecorder_ICallRecorderContainer $mock)
	{
		$verifier = self::getPhake()->verify($mock);

		return new Phake_Proxies_VerifierProxy($verifier, new Phake_Matchers_Factory());
	}

	/**
	 * Returns a new stubber for the given mock object.
	 * @param Phake_Stubber_IStubbable $mock
	 * @return Phake_Proxies_StubberProxy
	 */
	public static function when(Phake_Stubber_IStubbable $mock)
	{
		return new Phake_Proxies_StubberProxy($mock, new Phake_Matchers_Factory());
	}

	/**
	 * @param Phake_Facade $phake
	 */
	public static function setPhake(Phake_Facade $phake)
	{
		self::$phake = $phake;
	}

	/**
	 *
	 * @return Phake_Facade
	 */
	public static function getPhake()
	{
		if (empty(self::$phake))
		{
			self::setPhake(self::createPhake());
		}

		return self::$phake;
	}

	/**
	 * @return Phake_Facade
	 */
	public static function createPhake()
	{
		return new Phake_Facade();
	}

	/**
	 * Returns an equals matcher for the given value.
	 * @param mixed $value
	 * @return Phake_Matchers_EqualsMatcher
	 */
	public static function equalTo($value)
	{
		return new Phake_Matchers_EqualsMatcher($value);
	}
}
?>
