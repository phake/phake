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
require_once 'Phake/CallRecorder/OrderVerifier.php';
require_once 'Phake/Proxies/VerifierProxy.php';
require_once 'Phake/Proxies/StubberProxy.php';
require_once 'Phake/Proxies/AnswerBinderProxy.php';
require_once 'Phake/Matchers/EqualsMatcher.php';
require_once 'Phake/Matchers/ArgumentCaptor.php';
require_once 'Phake/Matchers/Factory.php';
require_once 'Phake/Stubber/SelfBindingAnswerBinder.php';
require_once 'Phake/Stubber/Answers/StaticAnswer.php';
require_once 'Phake/Stubber/Answers/SpyDelegate.php';

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
	 * @param Phake_Stubber_SelfBindingAnswerBinder $defaultAnswer
	 * @return Phake_ITestDouble
	 */
	public static function mock($className, Phake_Stubber_SelfBindingAnswerBinder $defaultAnswer = NULL)
	{
		if ($defaultAnswer === NULL)
		{
			$answer = new Phake_Stubber_Answers_StaticAnswer(NULL);
		}
		else
		{
			$answer = $defaultAnswer->getAnswer();
		}

		return self::getPhake()->mock($className, new Phake_ClassGenerator_MockClass(), new Phake_CallRecorder_Recorder(), $answer);
	}

	/**
	 * Returns a new spy object that watches the given object.
	 * @param object $spiedOn
	 * @return Phake_ITestDouble
	 */
	public static function spy($spiedOn)
	{
		$answer = new Phake_Stubber_Answers_SpyDelegate($spiedOn);

		return self::getPhake()->mock(get_class($spiedOn), new Phake_ClassGenerator_MockClass(), new Phake_CallRecorder_Recorder(), $answer);
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
	 * Allows verification of methods in a particular order
	 */
	public static function inOrder()
	{
		$calls = func_get_args();
		$orderVerifier = new Phake_CallRecorder_OrderVerifier();

		if (!$orderVerifier->verifyCallsInOrder(self::pullPositionsFromCallInfos($calls)))
		{
			throw new Exception("Calls not made in order");
		}
	}

	/**
	 * Allows for verifying that a mock object has no further calls made to it.
	 * @param Phake_IMock $mock
	 */
	public static function verifyNoFurtherInteraction(Phake_IMock $mock)
	{
		$mock->__PHAKE_freezeMock();
	}

	/**
	 * Allows for verifying that no interaction occured with a mock object
	 * @param Phake_IMock $mock
	 */
	public static function verifyNoInteraction(Phake_IMock $mock)
	{
		$callRecorder = $mock->__PHAKE_getCallRecorder();
		
		if (count($callRecorder->getAllCalls()))
		{
			throw new Exception('Calls should not have been made against this mock');
		}
	}

	/**
	 * Converts a bunch of call info objects to position objects.
	 * @param array $calls
	 * @return array
	 */
	private static function pullPositionsFromCallInfos(array $calls)
	{
		$transformed = array();
		foreach ($calls as $callList)
		{
			$transformedList = array();
			foreach ($callList as $call)
			{
				$transformedList[] = $call->getPosition();
			}
			$transformed[] = $transformedList;
		}
		return $transformed;
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
	 * Resets all calls and stubs on the given mock object
	 * @param Phake_IMock $mock
	 */
	public static function reset(Phake_IMock $mock)
	{
		$mock->__PHAKE_resetMock();
	}

	/**
	 * Provides methods for creating answers. Used in the api as a fluent way to set default stubs.
	 * @return Phake_Proxies_AnswerBinderProxy
	 */
	public static function ifUnstubbed()
	{
		$binder = new Phake_Stubber_SelfBindingAnswerBinder();
		return new Phake_Proxies_AnswerBinderProxy($binder);
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

	/**
	 * Returns a capturing matcher that will set the value of a given argument to given variable.
	 * @param mixed $value - Will be set to the value of the called argument.
	 * @return Phake_Matchers_ArgumentCaptor
	 */
	public static function capture(&$value)
	{
		return new Phake_Matchers_ArgumentCaptor($value);
	}
}
?>
