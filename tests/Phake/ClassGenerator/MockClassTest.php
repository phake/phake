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
        $mockedClass  = 'stdClass';

        $this->assertFalse(
            class_exists($newClassName, false),
            'The class being tested for already exists. May have created a test reusing this class name.'
        );

        $this->classGen->generate($newClassName, $mockedClass);

        $this->assertTrue(
            class_exists($newClassName, false),
            'Phake_ClassGenerator_MockClass::generate() did not create correct class'
        );
    }

    /**
     * Tests that the generate method will create a class that extends a given class.
     */
    public function testGenerateCreatesClassExtendingExistingClass()
    {
        $newClassName = __CLASS__ . '_TestClass2';
        $mockedClass  = 'stdClass';

        $this->classGen->generate($newClassName, $mockedClass);

        $rflClass = new ReflectionClass($newClassName);

        $this->assertTrue(
            $rflClass->isSubclassOf($mockedClass),
            'Phake_ClassGenerator_MockClass::generate() did not create a class that extends mocked class.'
        );
    }

    /**
     * Tests that generated mock classes will accept and provide access too a call recorder.
     */
    public function testGenerateCreatesClassWithExposedCallRecorder()
    {
        $newClassName = __CLASS__ . '_TestClass3';
        $mockedClass  = 'stdClass';

        $this->classGen->generate($newClassName, $mockedClass);

        $callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
        $stubMapper   = $this->getMock('Phake_Stubber_StubMapper');
        $answer       = $this->getMock('Phake_Stubber_IAnswer');
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        $this->assertSame($callRecorder, $mock->__PHAKE_callRecorder);
    }

    /**
     * Tests that generated mock classes will record calls to mocked methods.
     */
    public function testCallingMockedMethodRecordsCall()
    {
        $newClassName = __CLASS__ . '_TestClass4';
        $mockedClass  = 'PhakeTest_MockedClass';

        $this->classGen->generate($newClassName, $mockedClass);

        $callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
        $stubMapper   = $this->getMock('Phake_Stubber_StubMapper');
        $answer       = $this->getMock('Phake_Stubber_IAnswer');
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        /* @var $callRecorder Phake_CallRecorder_Recorder */
        $callRecorder->expects($this->once())
            ->method('recordCall')
            ->with($this->equalTo(new Phake_CallRecorder_Call($mock, 'foo', array(), new Phake_MockReader())));

        $mock->foo();
    }

    /**
     * Tests that calls are recorded with arguments
     */
    public function testCallingmockedMethodRecordsArguments()
    {
        $newClassName = __CLASS__ . '_TestClass9';
        $mockedClass  = 'PhakeTest_MockedClass';

        $this->classGen->generate($newClassName, $mockedClass);

        $callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
        $stubMapper   = $this->getMock('Phake_Stubber_StubMapper');
        $answer       = $this->getMock('Phake_Stubber_IAnswer');
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        /* @var $callRecorder Phake_CallRecorder_Recorder */
        $callRecorder->expects($this->once())
            ->method('recordCall')
            ->with(
                $this->equalTo(
                    new Phake_CallRecorder_Call($mock, 'fooWithArgument', array('bar'), new Phake_MockReader())
                )
            );

        $mock->fooWithArgument('bar');
    }

    /**
     * Tests the instantiation functionality of the mock generator.
     */
    public function testInstantiate()
    {
        $newClassName = __CLASS__ . '_TestClass5';
        $mockedClass  = 'PhakeTest_MockedClass';

        $this->classGen->generate($newClassName, $mockedClass);

        $callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
        $stubMapper   = $this->getMock('Phake_Stubber_StubMapper');
        $answer       = $this->getMock('Phake_Stubber_IAnswer');
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        $this->assertInstanceOf($newClassName, $mock);
    }

    /**
     * Tests that calling a stubbed method will result in the stubbed answer being returned.
     */
    public function testStubbedMethodsReturnStubbedAnswer()
    {
        $newClassName = __CLASS__ . '_TestClass7';
        $mockedClass  = 'PhakeTest_MockedClass';

        $this->classGen->generate($newClassName, $mockedClass);

        $callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
        $stubMapper   = $this->getMock('Phake_Stubber_StubMapper');
        $answer       = $this->getMock('Phake_Stubber_IAnswer');
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        $answer = $this->getMock('Phake_Stubber_IAnswer');

        $answer->expects($this->once())
            ->method('getAnswer');

        $stubMapper->expects($this->once())
            ->method('getStubByCall')
            ->with($this->equalTo('fooWithArgument'), array('bar'))
            ->will($this->returnValue(new Phake_Stubber_AnswerCollection($answer)));

        $mock->fooWithArgument('bar');
    }

    /**
     * Tests that default parameters work correctly with stubbing
     */
    public function testStubbedMethodDoesNotCheckUnpassedDefaultParameters()
    {
        $newClassName = __CLASS__ . '_TestClass23';
        $mockedClass  = 'PhakeTest_MockedClass';

        $this->classGen->generate($newClassName, $mockedClass);

        $callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
        $stubMapper   = $this->getMock('Phake_Stubber_StubMapper');
        $answer       = $this->getMock('Phake_Stubber_IAnswer');
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        $answer = $this->getMock('Phake_Stubber_IAnswer');

        $answer->expects($this->once())
            ->method('getAnswer');

        $stubMapper->expects($this->once())
            ->method('getStubByCall')
            ->with($this->equalTo('fooWithDefault'), array())
            ->will($this->returnValue(new Phake_Stubber_AnswerCollection($answer)));

        $mock->fooWithDefault();
    }

    /**
     * Tests that generated mock classes will allow setting stubs to methods. This is delegated
     * internally to the stubMapper
     */
    public function testStubbableInterface()
    {
        $newClassName = __CLASS__ . '_TestClass8';
        $mockedClass  = 'stdClass';

        $this->classGen->generate($newClassName, $mockedClass);

        /** @var $callRecorder Phake_CallRecorder_Recorder */
        $callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
        /** @var $stubMapper Phake_Stubber_StubMapper */
        $stubMapper = $this->getMock('Phake_Stubber_StubMapper');
        $answer     = $this->getMock('Phake_Stubber_IAnswer');
        $mock       = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        $answer           = $this->getMock('Phake_Stubber_IAnswer');
        $answerCollection = new Phake_Stubber_AnswerCollection($answer);
        $matcher          = $this->getMock('Phake_Matchers_MethodMatcher', array(), array(), '', false);

        $stubMapper->expects($this->once())
            ->method('mapStubToMatcher')
            ->with($this->equalTo($answerCollection), $this->equalTo($matcher));

        $mock->__PHAKE_stubMapper->mapStubToMatcher($answerCollection, $matcher);
    }

    /**
     * Tests that calling an unstubbed method will result in the default answer being returned.
     */
    public function testUnstubbedMethodsReturnDefaultAnswer()
    {
        $newClassName = __CLASS__ . '_TestClass11';
        $mockedClass  = 'PhakeTest_MockedClass';

        $this->classGen->generate($newClassName, $mockedClass);

        $callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
        $stubMapper   = $this->getMock('Phake_Stubber_StubMapper');
        $answer       = $this->getMock('Phake_Stubber_IAnswer');

        $mock = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        $answer->expects($this->once())
            ->method('getAnswer');

        $mock->fooWithArgument('bar');
    }

    /**
     * Tests that __call on an unmatched method will return a default value
     */
    public function testUnstubbedCallReturnsDefaultAnswer()
    {
        $newClassName = __CLASS__ . '_TestClass19';
        $mockedClass  = 'PhakeTest_MagicClass';

        $this->classGen->generate($newClassName, $mockedClass);

        $callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
        $stubMapper   = $this->getMock('Phake_Stubber_StubMapper');
        $answer       = $this->getMock('Phake_Stubber_IAnswer');

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
        $mockedClass  = 'PhakeTest_MockedClass';

        $this->classGen->generate($newClassName, $mockedClass);

        $callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
        $stubMapper   = $this->getMock('Phake_Stubber_StubMapper');
        $answer       = $this->getMock('Phake_Stubber_IAnswer');
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        $answer   = $this->getMock('Phake_Stubber_Answers_IDelegator');
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
            ->with($this->equalTo($mock), $this->equalTo('fooWithArgument'), $this->equalTo(array('bar')))
            ->will($this->returnValue(array($realAnswer, 'fooWithArgument')));

        $delegate->expects($this->any())
            ->method('getArguments')
            ->with($this->equalTo('fooWithArgument'), $this->equalTo(array('bar')))
            ->will($this->returnValue(array('bar')));

        $stubMapper->expects($this->once())
            ->method('getStubByCall')
            ->with($this->equalTo('fooWithArgument'), array('bar'))
            ->will($this->returnValue(new Phake_Stubber_AnswerCollection($answer)));

        $mock->fooWithArgument('bar');
    }

    /**
     * Tests that calling a stubbed method will result in the stubbed answer being returned.
     */
    public function testStubbedCallMethodsCallDelegatedAnswer()
    {
        $newClassName = __CLASS__ . '_TestClass20';
        $mockedClass  = 'PhakeTest_MagicClass';

        $this->classGen->generate($newClassName, $mockedClass);

        $callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
        $stubMapper   = $this->getMock('Phake_Stubber_StubMapper');
        $answer       = $this->getMock('Phake_Stubber_IAnswer');
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        $answer   = $this->getMock('Phake_Stubber_Answers_IDelegator');
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
            ->with(
                $this->equalTo($mock),
                $this->equalTo('__call'),
                $this->equalTo(array('fooWithArgument', array('bar')))
            )
            ->will($this->returnValue(array($realAnswer, 'fooWithArgument')));

        $delegate->expects($this->any())
            ->method('getArguments')
            ->with($this->equalTo('__call'), $this->equalTo(array('fooWithArgument', array('bar'))))
            ->will($this->returnValue(array('bar')));

        $stubMapper->expects($this->once())
            ->method('getStubByCall')
            ->with($this->equalTo('fooWithArgument'), array('bar'))
            ->will($this->returnValue(new Phake_Stubber_AnswerCollection($answer)));

        $mock->fooWithArgument('bar');
    }

    public function testMagicCallMethodsRecordTwice()
    {
        $newClassName = __CLASS__ . '_TestClass21';
        $mockedClass  = 'PhakeTest_MagicClass';

        $this->classGen->generate($newClassName, $mockedClass);

        $callRecorder = Phake::mock('Phake_CallRecorder_Recorder');
        $stubMapper   = $this->getMock('Phake_Stubber_StubMapper');
        $answer       = $this->getMock('Phake_Stubber_IAnswer');
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        $mock->foo('blah');

        Phake::verify($callRecorder)->recordCall(
            new Phake_CallRecorder_Call($mock, 'foo', array('blah'), new Phake_MockReader())
        );
        Phake::verify($callRecorder)->recordCall(
            new Phake_CallRecorder_Call($mock, '__call', array('foo', array('blah')), new Phake_MockReader())
        );
    }

    public function testMagicCallChecksFallbackStub()
    {
        $newClassName = __CLASS__ . '_TestClass22';
        $mockedClass  = 'PhakeTest_MagicClass';

        $this->classGen->generate($newClassName, $mockedClass);

        $callRecorder = Phake::mock('Phake_CallRecorder_Recorder');
        $stubMapper   = Phake::mock('Phake_Stubber_StubMapper');
        $answer       = $this->getMock('Phake_Stubber_IAnswer');
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);


        $mock->foo('blah');

        Phake::verify($stubMapper)->getStubByCall('foo', array('blah'));
        Phake::verify($stubMapper)->getStubByCall('__call', array('foo', array('blah')));
    }

    /**
     * Tests generating a class definition for a mocked interface
     */
    public function testGenerateOnInterface()
    {
        $newClassName = __CLASS__ . '_TestClass13';
        $mockedClass  = 'PhakeTest_MockedInterface';

        $this->classGen->generate($newClassName, $mockedClass);

        $this->assertTrue(
            class_exists($newClassName, false),
            'Phake_ClassGenerator_MockClass::generate() did not create correct class'
        );
    }

    /**
     * Test retrieving mock name
     */
    public function testMockName()
    {
        $newClassName = __CLASS__ . '_TestClass18';
        $mockedClass  = 'PhakeTest_MockedClass';

        $this->classGen->generate($newClassName, $mockedClass);

        $callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
        $stubMapper   = $this->getMock('Phake_Stubber_StubMapper');
        $answer       = $this->getMock('Phake_Stubber_IAnswer');
        $mock         = $this->classGen->instantiate($newClassName, $callRecorder, $stubMapper, $answer);

        $this->assertEquals('PhakeTest_MockedClass', $mock->__PHAKE_name);
    }

    /**
     * Tests that passing constructor arguments to the derived class will cause the original constructor to be called.
     */
    public function testCallingOriginalConstructor()
    {
        $newClassName = __CLASS__ . '_TestClass16';
        $mockedClass  = 'PhakeTest_MockedConstructedClass';
        $this->classGen->generate($newClassName, $mockedClass);

        /** @var $callRecorder Phake_CallRecorder_Recorder */
        $callRecorder = $this->getMock('Phake_CallRecorder_Recorder');
        /** @var $stubMapper Phake_Stubber_StubMapper */
        $stubMapper = $this->getMock('Phake_Stubber_StubMapper');
        $answer     = new Phake_Stubber_Answers_ParentDelegate();
        $mock       = $this->classGen->instantiate(
            $newClassName,
            $callRecorder,
            $stubMapper,
            $answer,
            array('val1', 'val2', 'val3')
        );

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
        $mockedClass  = 'PhakeTest_FinalMethod';

        $this->classGen->generate($newClassName, $mockedClass);

        $this->addToAssertionCount(1);
    }

    /**
     * Tests that the mocked object's __toString() method returns a string by default.
     */
    public function testToStringReturnsString()
    {
        $newClassName = __CLASS__ . '_TestClass24';
        $mockedClass  = 'PhakeTest_ToStringMethod';
        $this->classGen->generate($newClassName, $mockedClass);

        /** @var $recorder Phake_CallRecorder_Recorder */
        $recorder = $this->getMock('Phake_CallRecorder_Recorder');
        $mapper   = new Phake_Stubber_StubMapper();
        $answer   = new Phake_Stubber_Answers_ParentDelegate();

        $mock = $this->classGen->instantiate($newClassName, $recorder, $mapper, $answer);

        $string = $mock->__toString();

        $this->assertNotNull($string, '__toString() should not return NULL');
        $this->assertEquals('Mock for PhakeTest_ToStringMethod', $string);
    }

    public function testDestructMocked()
    {
        $newClassName = __CLASS__ . '_TestClass' . uniqid();
        $mockedClass  = 'PhakeTest_DestructorClass';
        $this->classGen->generate($newClassName, $mockedClass);

        /** @var $recorder Phake_CallRecorder_Recorder */
        $recorder = $this->getMock('Phake_CallRecorder_Recorder');
        $mapper   = new Phake_Stubber_StubMapper();
        $answer   = new Phake_Stubber_Answers_ParentDelegate();

        $mock = $this->classGen->instantiate($newClassName, $recorder, $mapper, $answer);

        unset($mock);
    }

    public function testMocksTraversable()
    {
        $this->assertInstanceOf('Traversable', Phake::mock('Traversable'));
    }

    public function testTraversableExtendedInterfaceIncludesOriginalInterface()
    {
        $this->assertInstanceOf('PhakeTest_TraversableInterface', Phake::mock('PhakeTest_TraversableInterface'));
    }
}


