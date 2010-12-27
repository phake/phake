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

require_once 'Phake/ClassGenerator/MockClass.php';
require_once 'Phake/CallRecorder/Recorder.php';
require_once 'Phake/Stubber/StubMapper.php';
require_once 'Phake/Stubber/IAnswer.php';
require_once 'Phake/Stubber/Answers/ParentDelegate.php';
require_once 'Phake/Stubber/IAnswerDelegate.php';

require_once 'PhakeTest/MockedClass.php';
require_once 'PhakeTest/MockedConstructedClass.php';
require_once 'PhakeTest/MockedInterface.php';
require_once 'PhakeTest/FinalMethod.php';

/**
 * Description of MockClass
 *
 * @todo Fix the instiate calls that are doing exactly the same basic thing...
 */
class Phake_ClassGenerator_MockClassTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var Phake_ClassGenerator_MockClass
	 */
	private $classGen;

	public function setUp()
	{
		$this->classGen = new Phake_ClassGenerator_MockClass();
	}

	/**
	 * Tests the generate method of the mock class generator.
	 */
	public function testGenerateCreatesClass()
	{
		$newClassName = __CLASS__ . '_TestClass1';
		$mockedClass = 'stdClass';

		$this->assertFalse(
			class_exists($newClassName, FALSE),
			'The class being tested for already exists. May have created a test reusing this class name.');

		$this->classGen->generate($newClassName, $mockedClass);

		$this->assertTrue(
			class_exists($newClassName, FALSE),
			'Phake_ClassGenerator_MockClass::generate() did not create correct class');
	}

	/**
	 * Tests that the generate method will create a class that extends a given class.
	 */
	public function testGenerateCreatesClassExtendingExistingClass()
	{
		$newClassName = __CLASS__ . '_TestClass2';
		$mockedClass = 'stdClass';

		$this->classGen->generate($newClassName, $mockedClass);

		$rflClass = new ReflectionClass($newClassName);

		$this->assertTrue(
			$rflClass->isSubclassOf($mockedClass),
			'Phake_ClassGenerator_MockClass::generate() did not create a class that extends mocked class.');
	}

	/**
	 * Tests that generated mock classes will accept and provide access too a call recorder.
	 */
	public function testGenerateCreatesClassWithExposedCallRecorder()
	{
		$newClassName = __CLASS__ . '_TestClass3';
		$mockedClass = 'stdClass';

		$this->classGen->generate($newClassName, $mockedClass);

		$callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
		$stubMapper = $this->getMock('Phake_Stubber_StubMapper');
		$answer = $this->getMock('Phake_Stubber_IAnswer');
		$mock = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

		$this->assertSame($callRecorder, $mock->__PHAKE_getCallRecorder());
	}

	/**
	 * Tests that generated mock classes will record calls to mocked methods.
	 */
	public function testCallingMockedMethodRecordsCall()
	{
		$newClassName = __CLASS__ . '_TestClass4';
		$mockedClass = 'PhakeTest_MockedClass';

		$this->classGen->generate($newClassName, $mockedClass);

		$callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
		$stubMapper = $this->getMock('Phake_Stubber_StubMapper');
		$answer = $this->getMock('Phake_Stubber_IAnswer');
		$mock = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

		/* @var $callRecorder Phake_CallRecorder_Recorder */
		$callRecorder->expects($this->once())
			->method('recordCall')
			->with($this->equalTo(new Phake_CallRecorder_Call($mock, 'foo', array())));

		$mock->foo();
	}

	/**
	 * Tests that calls are recorded with arguments
	 */
	public function testCallingmockedMethodRecordsArguments()
	{
		$newClassName = __CLASS__ . '_TestClass9';
		$mockedClass = 'PhakeTest_MockedClass';

		$this->classGen->generate($newClassName, $mockedClass);

		$callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
		$stubMapper = $this->getMock('Phake_Stubber_StubMapper');
		$answer = $this->getMock('Phake_Stubber_IAnswer');
		$mock = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

		/* @var $callRecorder Phake_CallRecorder_Recorder */
		$callRecorder->expects($this->once())
			->method('recordCall')
			->with($this->equalTo(new Phake_CallRecorder_Call($mock, 'fooWithArgument', array('bar'))));

		$mock->fooWithArgument('bar');
	}

	/**
	 * Tests the instantiation functionality of the mock generator.
	 */
	public function testInstantiate()
	{
		$newClassName = __CLASS__ . '_TestClass5';
		$mockedClass = 'PhakeTest_MockedClass';

		$this->classGen->generate($newClassName, $mockedClass);

		$callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
		$stubMapper = $this->getMock('Phake_Stubber_StubMapper');
		$answer = $this->getMock('Phake_Stubber_IAnswer');
		$mock = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

		$this->assertType($newClassName, $mock);
	}

	/**
	 * Tests that calling a stubbed method will result in the stubbed answer being returned.
	 */
	public function testStubbedMethodsReturnStubbedAnswer()
	{
		$newClassName = __CLASS__ . '_TestClass7';
		$mockedClass = 'PhakeTest_MockedClass';

		$this->classGen->generate($newClassName, $mockedClass);

		$callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
		$stubMapper = $this->getMock('Phake_Stubber_StubMapper');
		$answer = $this->getMock('Phake_Stubber_IAnswer');
		$mock = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

		$answer = $this->getMock('Phake_Stubber_IAnswer');

		$answer->expects($this->once())
			->method('getAnswer');

		$stubMapper->expects($this->once())
			->method('getStubByCall')
			->with($this->equalTo('fooWithArgument'), array('bar'))
			->will($this->returnValue($answer));

		$mock->fooWithArgument('bar');
	}

	/**
	 * Tests that generated mock classes will allow setting stubs to methods. This is delegated
	 * internally to the stubMapper
	 */
	public function testStubbableInterface()
	{
		$newClassName = __CLASS__ . '_TestClass8';
		$mockedClass = 'stdClass';

		$this->classGen->generate($newClassName, $mockedClass);

		$callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
		$stubMapper = $this->getMock('Phake_Stubber_StubMapper');
		$answer = $this->getMock('Phake_Stubber_IAnswer');
		$mock = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

		$answer = $this->getMock('Phake_Stubber_IAnswer');
		$matcher = $this->getMock('Phake_Matchers_MethodMatcher', array(), array(), '', FALSE);

		$stubMapper->expects($this->once())
			->method('mapStubToMatcher')
			->with($this->equalTo($answer), $this->equalTo($matcher));

		$mock->__PHAKE_addAnswer($answer, $matcher);
	}

	/**
	 * Tests that resetting a mock will remove all answers and calls from the stub mapper and call
	 * recorder
	 */
	public function testReset()
	{
		$newClassName = __CLASS__ . '_TestClass10';
		$mockedClass = 'stdClass';

		$this->classGen->generate($newClassName, $mockedClass);

		$callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
		$stubMapper = $this->getMock('Phake_Stubber_StubMapper');
		$answer = $this->getMock('Phake_Stubber_IAnswer');
		$mock = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

		$callRecorder->expects($this->once())
						->method('removeAllCalls');

		$stubMapper->expects($this->once())
						->method('removeAllAnswers');

		$mock->__PHAKE_resetMock();
	}

	/**
	 * Tests that calling an unstubbed method will result in the default answer being returned.
	 */
	public function testUnstubbedMethodsReturnDefaultAnswer()
	{
		$newClassName = __CLASS__ . '_TestClass11';
		$mockedClass = 'PhakeTest_MockedClass';

		$this->classGen->generate($newClassName, $mockedClass);

		$callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
		$stubMapper = $this->getMock('Phake_Stubber_StubMapper');
		$answer = $this->getMock('Phake_Stubber_IAnswer');

		$mock = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

		$answer->expects($this->once())
			->method('getAnswer');

		$mock->fooWithArgument('bar');
	}


	/**
	 * Tests that calling a stubbed method will result in the stubbed answer being returned.
	 */
	public function testStubbedMethodsCallDelegatedAnswer()
	{
		$newClassName = __CLASS__ . '_TestClass12';
		$mockedClass = 'PhakeTest_MockedClass';

		$this->classGen->generate($newClassName, $mockedClass);

		$callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
		$stubMapper = $this->getMock('Phake_Stubber_StubMapper');
		$answer = $this->getMock('Phake_Stubber_IAnswer');
		$mock = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

		$answer = $this->getMock('Phake_Stubber_Answers_IDelegator');
		$delegate = $this->getMock('Phake_Stubber_IAnswerDelegate');

		$answer->expects($this->once())
			->method('getAnswer')
			->will($this->returnValue($delegate));

		$answer->expects($this->once())
			->method('processAnswer')
			->with(null);

		$realAnswer = $this->getMock('PhakeTest_MockedClass');

		$realAnswer->expects($this->once())
						->method('fooWithArgument')
						->with($this->equalTo('bar'));

		$delegate->expects($this->any())
						->method('getCallBack')
						->with($this->equalTo('fooWithArgument'), $this->equalTo(array('bar')))
						->will($this->returnValue(array($realAnswer, 'fooWithArgument')));

		$delegate->expects($this->any())
						->method('getArguments')
						->with($this->equalTo('fooWithArgument'), $this->equalTo(array('bar')))
						->will($this->returnValue(array('bar')));

		$stubMapper->expects($this->once())
			->method('getStubByCall')
			->with($this->equalTo('fooWithArgument'), array('bar'))
			->will($this->returnValue($answer));

		$mock->fooWithArgument('bar');
	}

	/**
	 * Tests generating a class definition for a mocked interface
	 */
	public function testGenerateOnInterface()
	{
		$newClassName = __CLASS__ . '_TestClass13';
		$mockedClass = 'PhakeTest_MockedInterface';

		$this->classGen->generate($newClassName, $mockedClass);

		$this->assertTrue(
			class_exists($newClassName, FALSE),
			'Phake_ClassGenerator_MockClass::generate() did not create correct class');
	}

	/**
	 * Tests that calling the freeze method on a mocked class will result in further calls throwing
	 * an exception
	 */
	public function testMockFreezing()
	{
		$newClassName = __CLASS__ . '_TestClass14';
		$mockedClass = 'PhakeTest_MockedClass';

		$this->classGen->generate($newClassName, $mockedClass);

		$callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
		$stubMapper = $this->getMock('Phake_Stubber_StubMapper');
		$answer = $this->getMock('Phake_Stubber_IAnswer');
		$mock = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

		$mock->__PHAKE_freezeMock();

		$this->setExpectedException('Exception');

		$mock->foo();
	}

	/**
	 * Tests that calling the freeze method on a mocked class will not throw an error if the mock
	 * is reset
	 */
	public function testMockFreezingResets()
	{
		$newClassName = __CLASS__ . '_TestClass15';
		$mockedClass = 'PhakeTest_MockedClass';

		$this->classGen->generate($newClassName, $mockedClass);

		$callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
		$stubMapper = $this->getMock('Phake_Stubber_StubMapper');
		$answer = $this->getMock('Phake_Stubber_IAnswer');
		$mock = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

		$mock->__PHAKE_freezeMock();
		$mock->__PHAKE_resetMock();

		$mock->foo();
	}

	/**
	 * Tests that passing constructor arguments to the derived class will cause the original constructor to be called.
	 */
	public function testCallingOriginalConstructor()
	{
		$newClassName = __CLASS__ . '_TestClass16';
		$mockedClass = 'PhakeTest_MockedConstructedClass';
		$this->classGen->generate($newClassName, $mockedClass);

		$callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
		$stubMapper = $this->getMock('Phake_Stubber_StubMapper');
		$answer = new Phake_Stubber_Answers_ParentDelegate();
		$mock = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer, array('val1', 'val2', 'val3'));

		$this->assertEquals('val1', $mock->getProp1());
		$this->assertEquals('val2', $mock->getProp2());
		$this->assertEquals('val3', $mock->getProp3());
	}

	/**
	 * Tests that final methods are not overridden
	 */
	public function testFinalMethodsAreNotOverridden()
	{
		$newClassName = __CLASS__ . '_TestClass17';
		$mockedClass = 'PhakeTest_FinalMethod';

		$this->classGen->generate($newClassName, $mockedClass);
	}
}
?>
