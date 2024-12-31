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

namespace Phake\ClassGenerator;

use Phake;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Description of MockClass
 */
class MockClassTest extends TestCase
{
    private MockClass $classGen;

    /**
     * @Mock
     */
    private Phake\Mock\InfoRegistry $infoRegistry;

    public function setUp(): void
    {
        Phake::initAnnotations($this);
        $this->classGen = Phake::getMockClassGenerator();
        $this->infoRegistry = Phake::getPhake()->getInfoRegistry();
    }

    /**
     * Tests the generate method of the mock class generator.
     */
    public function testGenerateCreatesClass(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass1';
        $mockedClass  = \stdClass::class;

        $this->assertFalse(
            class_exists($newClassName, false),
            'The class being tested for already exists. May have created a test reusing this class name.'
        );

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $this->assertTrue(
            class_exists($newClassName, false),
            'Phake\ClassGenerator\MockClass::generate() did not create correct class'
        );
    }

    /**
     * Tests that the generate method will create a class that extends a given class.
     */
    public function testGenerateCreatesClassExtendingExistingClass(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass2';
        $mockedClass  = \stdClass::class;

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $rflClass = new \ReflectionClass($newClassName);

        $this->assertTrue(
            $rflClass->isSubclassOf($mockedClass),
            'Phake\ClassGenerator\MockClass::generate() did not create a class that extends mocked class.'
        );
    }

    /**
     * Tests that generated mock classes will accept and provide access too a call recorder.
     */
    public function testGenerateCreatesClassWithExposedCallRecorder(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass3';
        $mockedClass  = \stdClass::class;

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = $this->getMockBuilder(Phake\Stubber\IAnswer::class)->getMock();
        $mock         = $this->classGen->instantiate($newClassName, $this->infoRegistry, $callRecorder, $stubMapper, $answer);

        $this->assertSame($callRecorder, Phake::getInfo($mock)->getCallRecorder());
    }

    /**
     * Tests that generated mock classes will record calls to mocked methods.
     */
    public function testCallingMockedMethodRecordsCall(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass4';
        $mockedClass  = \PhakeTest_MockedClass::class;

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = new Phake\Stubber\Answers\NoAnswer();
        $mock         = $this->classGen->instantiate($newClassName, $this->infoRegistry, $callRecorder, $stubMapper, $answer);

        assert($callRecorder instanceof Phake\CallRecorder\Recorder);
        $callRecorder->expects($this->once())
            ->method('recordCall')
            ->with($this->equalTo(new Phake\CallRecorder\Call($mock, 'foo', [])));

        $mock->foo();
    }

    /**
     * Tests that calls are recorded with arguments
     */
    public function testCallingmockedMethodRecordsArguments(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass9';
        $mockedClass  = \PhakeTest_MockedClass::class;

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = new Phake\Stubber\Answers\NoAnswer();
        $mock         = $this->classGen->instantiate($newClassName, $this->infoRegistry, $callRecorder, $stubMapper, $answer);

        assert($callRecorder instanceof Phake\CallRecorder\Recorder);
        $callRecorder->expects($this->once())
            ->method('recordCall')
            ->with(
                $this->equalTo(
                    new Phake\CallRecorder\Call($mock, 'fooWithArgument', ['bar'])
                )
            );

        $mock->fooWithArgument('bar');
    }

    public function testGeneratingClassFromMultipleInterfaces(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_testClass28';
        $mockedClass = [\PhakeTest_MockedInterface::class, \PhakeTest_ConstructorInterface::class];

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $reflClass = new \ReflectionClass($newClassName);
        $this->assertTrue($reflClass->implementsInterface(\PhakeTest_MockedInterface::class), 'Implements PhakeTest_MockedInterface');
        $this->assertTrue($reflClass->implementsInterface(\PhakeTest_ConstructorInterface::class), 'Implements PhakeTest_ConstructorInterface');
    }

    public function testGeneratingClassFromSimilarInterfaces(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_testClass29';
        $mockedClass = [\PhakeTest_MockedInterface::class, \PhakeTest_MockedInterface2::class];

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $reflClass = new \ReflectionClass($newClassName);
        $this->assertTrue($reflClass->implementsInterface(\PhakeTest_MockedInterface::class), 'Implements PhakeTest_MockedInterface');
        $this->assertTrue($reflClass->implementsInterface(\PhakeTest_MockedInterface2::class), 'Implements PhakeTest_ConstructorInterface');
    }

    public function testGeneratingClassFromDuplicateInterfaces(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_testClass30';
        $mockedClass = [\PhakeTest_MockedInterface::class, \PhakeTest_MockedInterface::class];

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $reflClass = new \ReflectionClass($newClassName);
        $this->assertTrue($reflClass->implementsInterface(\PhakeTest_MockedInterface::class), 'Implements PhakeTest_MockedInterface');
    }

    public function testGeneratingClassFromInheritedInterfaces(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_testClass31';
        $mockedClass = [\PhakeTest_MockedInterface::class, \PhakeTest_MockedChildInterface::class];

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $reflClass = new \ReflectionClass($newClassName);
        $this->assertTrue($reflClass->implementsInterface(\PhakeTest_MockedInterface::class), 'Implements PhakeTest_MockedInterface');
        $this->assertTrue($reflClass->implementsInterface(\PhakeTest_MockedChildInterface::class), 'Implements PhakeTest_MockedInterface');
    }

    public function testGeneratingClassFromMultipleClasses(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_testClass32';
        $mockedClass = [\PhakeTest_MockedClass::class, \PhakeTest_MockedConstructedClass::class];

        $this->expectException('RuntimeException');
        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);
    }

    /**
     * Tests the instantiation functionality of the mock generator.
     */
    public function testInstantiate(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass5';
        $mockedClass  = \PhakeTest_MockedClass::class;

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = Phake::mock(Phake\Stubber\Answers\NoAnswer::class, Phake::ifUnstubbed()->thenCallParent());
        $mock         = $this->classGen->instantiate($newClassName, $this->infoRegistry, $callRecorder, $stubMapper, $answer);

        $this->assertInstanceOf($newClassName, $mock);
    }

    /**
     * Tests that calling a stubbed method will result in the stubbed answer being returned.
     * @group testonly
     */
    #[Group('testonly')]
    public function testStubbedMethodsReturnStubbedAnswer(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass7';
        $mockedClass  = \PhakeTest_MockedClass::class;

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = Phake::partialMock(Phake\Stubber\Answers\NoAnswer::class);
        $mock         = $this->classGen->instantiate($newClassName, $this->infoRegistry, $callRecorder, $stubMapper, $answer);

        $stubMapper->expects($this->once())
            ->method('getStubByCall')
            ->with($this->equalTo('fooWithArgument'), ['bar'])
            ->willReturn(new Phake\Stubber\AnswerCollection($answer));

        $mock->fooWithArgument('bar');

        Phake::verify($answer)->getAnswerCallback($mock, 'fooWithArgument');
    }

    /**
     * Tests that default parameters work correctly with stubbing
     */
    public function testStubbedMethodDoesNotCheckUnpassedDefaultParameters(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass23';
        $mockedClass  = \PhakeTest_MockedClass::class;

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = Phake::partialMock(Phake\Stubber\Answers\NoAnswer::class);
        $mock         = $this->classGen->instantiate($newClassName, $this->infoRegistry, $callRecorder, $stubMapper, $answer);

        $stubMapper->expects($this->once())
            ->method('getStubByCall')
            ->with($this->equalTo('fooWithDefault'), [])
            ->willReturn(new Phake\Stubber\AnswerCollection($answer));

        $mock->fooWithDefault();

        Phake::verify($answer)->getAnswerCallback($mock, 'fooWithDefault');
    }

    /**
     * Tests that generated mock classes will allow setting stubs to methods. This is delegated
     * internally to the stubMapper
     */
    public function testStubbableInterface(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass8';
        $mockedClass  = \stdClass::class;

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        assert($callRecorder instanceof Phake\CallRecorder\Recorder);
        $stubMapper = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        assert($stubMapper instanceof Phake\Stubber\StubMapper);
        $answer     = $this->getMockBuilder(Phake\Stubber\IAnswer::class)->getMock();
        $mock       = $this->classGen->instantiate($newClassName, $this->infoRegistry, $callRecorder, $stubMapper, $answer);

        $answer           = $this->getMockBuilder(Phake\Stubber\IAnswer::class)->getMock();
        $answerCollection = new Phake\Stubber\AnswerCollection($answer);
        $matcher          = $this->getMockBuilder(Phake\Matchers\MethodMatcher::class)
                                ->disableOriginalConstructor()
                                ->getMock();

        $stubMapper->expects($this->once())
            ->method('mapStubToMatcher')
            ->with($this->equalTo($answerCollection), $this->equalTo($matcher));

        Phake::getInfo($mock)->getStubMapper()->mapStubToMatcher($answerCollection, $matcher);
    }

    /**
     * Tests that calling an unstubbed method will result in the default answer being returned.
     */
    public function testUnstubbedMethodsReturnDefaultAnswer(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass11';
        $mockedClass  = \PhakeTest_MockedClass::class;

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = Phake::partialMock(Phake\Stubber\Answers\NoAnswer::class);

        $mock = $this->classGen->instantiate($newClassName, $this->infoRegistry, $callRecorder, $stubMapper, $answer);

        $mock->fooWithArgument('bar');

        Phake::verify($answer)->getAnswerCallback($mock, 'fooWithArgument');
    }

    /**
     * Tests that __call on an unmatched method will return a default value
     */
    public function testUnstubbedCallReturnsDefaultAnswer(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass19';
        $mockedClass  = \PhakeTest_MagicClass::class;

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = Phake::partialMock(Phake\Stubber\Answers\NoAnswer::class);

        $mock = $this->classGen->instantiate($newClassName, $this->infoRegistry, $callRecorder, $stubMapper, $answer);

        $mock->fooWithArgument('bar');

        Phake::verify($answer)->getAnswerCallback($mock, '__call');
    }

    public function testMagicCallMethodsRecordTwice(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass21';
        $mockedClass  = \PhakeTest_MagicClass::class;

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = Phake::mock(Phake\CallRecorder\Recorder::class);
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = Phake::partialMock(Phake\Stubber\Answers\NoAnswer::class);
        $mock         = $this->classGen->instantiate($newClassName, $this->infoRegistry, $callRecorder, $stubMapper, $answer);

        $mock->foo('blah');

        Phake::verify($callRecorder)->recordCall(
            $this->equalTo(new Phake\CallRecorder\Call($mock, 'foo', ['blah']))
        );
        Phake::verify($callRecorder)->recordCall(
            $this->equalTo(new Phake\CallRecorder\Call($mock, '__call', ['foo', ['blah']]))
        );
    }

    public function testMagicCallChecksFallbackStub(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass22';
        $mockedClass  = \PhakeTest_MagicClass::class;

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = Phake::mock(Phake\CallRecorder\Recorder::class);
        $stubMapper   = Phake::mock(Phake\Stubber\StubMapper::class);
        $answer       = Phake::partialMock(Phake\Stubber\Answers\NoAnswer::class);
        $mock         = $this->classGen->instantiate($newClassName, $this->infoRegistry, $callRecorder, $stubMapper, $answer);
        Phake::when($stubMapper)->getStubByCall->thenReturn(null);

        $mock->foo('blah');

        Phake::verify($stubMapper)->getStubByCall('foo', ['blah']);
        Phake::verify($stubMapper)->getStubByCall('__call', ['foo', ['blah']]);
    }

    /**
     * Tests generating a class definition for a mocked interface
     */
    public function testGenerateOnInterface(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass13';
        $mockedClass  = \PhakeTest_MockedInterface::class;

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $this->assertTrue(
            class_exists($newClassName, false),
            'Phake\ClassGenerator\MockClass::generate() did not create correct class'
        );
    }

    /**
     * Test retrieving mock name
     */
    public function testMockName(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass18';
        $mockedClass  = \PhakeTest_MockedClass::class;

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        $stubMapper   = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        $answer       = $this->getMockBuilder(Phake\Stubber\IAnswer::class)->getMock();
        $mock         = $this->classGen->instantiate($newClassName, $this->infoRegistry, $callRecorder, $stubMapper, $answer);

        $this->assertEquals(\PhakeTest_MockedClass::class, $mock::__PHAKE_name);
        $this->assertEquals(\PhakeTest_MockedClass::class, Phake::getInfo($mock)->getName());
    }

    /**
     * Tests that passing constructor arguments to the derived class will cause the original constructor to be called.
     */
    public function testCallingOriginalConstructor(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass16';
        $mockedClass  = \PhakeTest_MockedConstructedClass::class;
        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        assert($callRecorder instanceof Phake\CallRecorder\Recorder);
        $stubMapper = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        assert($stubMapper instanceof Phake\Stubber\StubMapper);
        $answer     = new Phake\Stubber\Answers\ParentDelegate();
        $mock       = $this->classGen->instantiate(
            $newClassName,
            $this->infoRegistry,
            $callRecorder,
            $stubMapper,
            $answer,
            ['val1', 'val2', 'val3']
        );

        $this->assertEquals('val1', $mock->getProp1());
        $this->assertEquals('val2', $mock->getProp2());
        $this->assertEquals('val3', $mock->getProp3());
    }

    /**
     * Tests that passing constructor arguments to the derived class will cause the original constructor to be called.
     */
    public function testCallingFinalOriginalConstructor(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass26';
        $mockedClass  = \PhakeTest_MockedFinalConstructedClass::class;
        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $callRecorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        assert($callRecorder instanceof Phake\CallRecorder\Recorder);
        $stubMapper = $this->getMockBuilder(Phake\Stubber\StubMapper::class)->getMock();
        assert($stubMapper instanceof Phake\Stubber\StubMapper);
        $answer     = new Phake\Stubber\Answers\ParentDelegate();
        $mock       = $this->classGen->instantiate(
            $newClassName,
            $this->infoRegistry,
            $callRecorder,
            $stubMapper,
            $answer,
            ['val1', 'val2', 'val3']
        );

        $this->assertEquals('val1', $mock->getProp1());
        $this->assertEquals('val2', $mock->getProp2());
        $this->assertEquals('val3', $mock->getProp3());
    }

    /**
     * Tests the generate method of the mock class generator.
     */
    public function testGenerateCreatesClassWithConstructorInInterfaceButNotInAbstractClass(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass27';
        $mockedClass = \PhakeTest_ImplementConstructorInterface::class;

        $this->assertFalse(
            class_exists($newClassName, false),
            'The class being tested for already exists. May have created a test reusing this class name.'
        );

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $this->assertTrue(
            class_exists($newClassName, false),
            'Phake\ClassGenerator\MockClass::generate() did not create correct class'
        );
    }

    /**
     * Tests that final methods are not overridden
     */
    public function testFinalMethodsAreNotOverridden(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass17';
        $mockedClass  = \PhakeTest_FinalMethod::class;

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $this->addToAssertionCount(1);
    }

    public function testMockFinalClassThrowsException(): void
    {
        $expectedException = new \InvalidArgumentException('Final classes cannot be mocked.');

        try {
            $mock = Phake::mock(\PhakeTest_FinalClass::class);
            $this->fail('The mocked final method did not throw an exception');
        } catch (\InvalidArgumentException $actualException) {
            $this->assertEquals($actualException, $expectedException);
        }
    }

    /**
     * Tests that the mocked object's __toString() method returns a string by default.
     */
    public function testToStringReturnsString(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass24';
        $mockedClass  = \PhakeTest_ToStringMethod::class;
        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $recorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        assert($recorder instanceof Phake\CallRecorder\Recorder);
        $mapper   = new Phake\Stubber\StubMapper();
        $answer   = new Phake\Stubber\Answers\ParentDelegate();

        $mock = $this->classGen->instantiate($newClassName, $this->infoRegistry, $recorder, $mapper, $answer);

        $string = $mock->__toString();

        $this->assertNotNull($string, '__toString() should not return NULL');
        $this->assertEquals('Mock for PhakeTest_ToStringMethod', $string);
    }

    public function testDestructMocked(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass' . bin2hex(random_bytes(7));
        $mockedClass  = \PhakeTest_DestructorClass::class;
        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $recorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        assert($recorder instanceof Phake\CallRecorder\Recorder);
        $mapper   = new Phake\Stubber\StubMapper();
        $answer   = new Phake\Stubber\Answers\ParentDelegate();

        $mock = $this->classGen->instantiate($newClassName, $this->infoRegistry, $recorder, $mapper, $answer);

        \PhakeTest_DestructorClass::$destructCalled = false;
        unset($mock);
        $this->assertFalse(\PhakeTest_DestructorClass::$destructCalled, 'It appears that the destructor was called');
    }

    public function testSerializableMock(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass' . uniqid();
        $mockedClass  = \PhakeTest_SerializableClass::class;
        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $recorder = $this->getMockBuilder(Phake\CallRecorder\Recorder::class)->getMock();
        assert($recorder instanceof Phake\CallRecorder\Recorder);
        $mapper   = new Phake\Stubber\StubMapper();
        $answer   = new Phake\Stubber\Answers\ParentDelegate();

        try {
            $mock = $this->classGen->instantiate($newClassName, $this->infoRegistry, $recorder, $mapper, $answer);
            $this->assertInstanceOf(\PhakeTest_SerializableClass::class, $mock);
        } catch (\Exception $e) {
            $this->fail("Can't instantiate Serializable Object");
        }
    }

    public function testMocksTraversable(): void
    {
        $this->assertInstanceOf('Traversable', Phake::mock('Traversable'));
    }

    public function testTraversableExtendedInterfaceIncludesOriginalInterface(): void
    {
        $this->assertInstanceOf(\PhakeTest_TraversableInterface::class, Phake::mock(\PhakeTest_TraversableInterface::class));
    }

    /**
     * Ensure that 'callable' type hints in method parameters are supported.
     */
    public function testCallableTypeHint(): void
    {
        $this->assertInstanceOf(\PhakeTest_CallableTypehint::class, Phake::mock(\PhakeTest_CallableTypehint::class));
    }

    public function testMockVariableNumberOfArguments(): void
    {
        $mockedClass = Phake::mock(\PhakeTest_MockedClass::class);
        [$arg1, $arg2, $arg3] = [1, 2, 3];
        $mockedClass->fooWithVariableNumberOfArguments($arg1, $arg2, $arg3);

        Phake::verify($mockedClass)->fooWithVariableNumberOfArguments(1, 2, 3);
    }

    public function testGeneratedMockClassHasStaticInfo(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass' . bin2hex(random_bytes(7));
        $mockedClass  = \stdClass::class;
        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $info = $this->infoRegistry->getInfo($newClassName);
        assert($info instanceof Phake\Mock\Info);
        $this->assertInstanceOf(Phake\Mock\Info::class, $info);

        $this->assertInstanceOf(Phake\Stubber\IAnswer::class, $info->getDefaultAnswer());
        $this->assertEquals($mockedClass, $info->getName());
        $this->assertInstanceOf(Phake\CallRecorder\Recorder::class, $info->getCallRecorder());
        $this->assertInstanceOf(Phake\Stubber\StubMapper::class, $info->getStubMapper());
        $this->assertInstanceOf(InvocationHandler\IInvocationHandler::class, $info->getHandlerChain());
    }

    public function testGeneratedMockAddsSelfToRegistry(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass' . uniqid();
        $mockedClass  = \stdClass::class;
        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $this->assertInstanceOf(Phake\Mock\Info::class, $this->infoRegistry->getInfo($newClassName));
    }

    /**
     * Test that the generated mock has the same doc mocked class
     */
    public function testGenerateMaintainsPhpDoc(): void
    {
        $newClassName = str_replace('\\', '_', self::class) . '_TestClass25';
        $mockedClass = \PhakeTest_MockedClass::class;

        $this->classGen->generate($newClassName, $mockedClass, $this->infoRegistry);

        $rflClass = new \ReflectionClass($newClassName);

        $this->assertFalse($rflClass->getMethod('foo')->getDocComment());
        $this->assertEquals(
            "/**\n     * @return void\n     */",
            $rflClass->getMethod('fooWithComment')->getDocComment()
        );
    }

    public function testStubbingVariadics(): void
    {
        $mock = Phake::mock(\PhakeTest_Variadic::class);

        Phake::when($mock)->variadicMethod->thenCallParent();

        $this->assertEquals([1, 2, 3, 4], $mock->variadicMethod(1, 2, 3, 4));
    }

    public function testMockingVariadics(): void
    {
        $mock = Phake::mock(\PhakeTest_Variadic::class);

        $mock->variadicMethod(1, 2, 3, 4, 5, 6);

        Phake::verify($mock)->variadicMethod(1, 2, 3, 4, 5, 6);
    }

    public function testStubbingScalarReturnHints(): void
    {
        $mock = Phake::mock(\PhakeTest_ScalarTypes::class);

        Phake::when($mock)->scalarHints->thenReturn(2);

        $this->assertEquals(2, $mock->scalarHints(1, 1));
    }

    public function testStubbingScalarReturnsWrongType(): void
    {
        $mock = Phake::mock(\PhakeTest_ScalarTypes::class);

        Phake::when($mock)->scalarHints->thenReturn([]);

        try {
            $this->assertEquals([], $mock->scalarHints(1, 1));
        } catch (\TypeError $e) {
            Phake::verify($mock)->scalarHints(1, 1);

            return;
        } catch (\Throwable $e) {
            $this->fail('Expected A Type Error, instead got ' . get_class($e) . " {$e}");
        }
        $this->fail('Expected A Type Error, no error received');
    }

    public function testDefaultStubChanged(): void
    {
        $mock = Phake::mock(\PhakeTest_ScalarTypes::class);

        $mock->scalarHints(1, 1);

        Phake::verify($mock)->scalarHints(1, 1);
    }

    public function testVoidStubReturnsProperly(): void
    {
        $mock = Phake::mock(\PhakeTest_VoidType::class);

        $this->assertNull($mock->voidMethod());

        Phake::verify($mock)->voidMethod();
    }

    public function testVoidStubThrowsException(): void
    {
        $mock = Phake::mock(\PhakeTest_VoidType::class);

        $expectedException = new \Exception('Test Exception');
        Phake::when($mock)->voidMethod->thenThrow($expectedException);

        try {
            $mock->voidMethod();
            $this->fail('The mocked void method did not throw an exception');
        } catch (\Exception $actualException) {
            $this->assertSame($expectedException, $actualException, 'The same exception was not thrown');
        }
    }

    public function testVoidStubCanCallParent(): void
    {
        $mock = Phake::mock(\PhakeTest_VoidType::class);

        Phake::when($mock)->voidMethod->thenCallParent();

        $mock->voidMethod();

        $this->assertEquals(1, $mock->voidCallCount, "Void call count was not incremented, looks like callParent doesn't work");
    }

    public function testStubbingNotNullableReturnHint(): void
    {
        $mock = Phake::mock(\PhakeTest_ScalarTypes::class);

        Phake::when($mock)->objectReturn->thenReturn(null);

        try {
            $mock->objectReturn();
            $this->fail('Expected TypeError');
        } catch (\TypeError $e) {
            $this->assertTrue(true);
        }
    }

    public function testStubbingNullableReturnHints(): void
    {
        $mock = Phake::mock(\PhakeTest_NullableTypes::class);
        Phake::when($mock)->objectReturn->thenReturn(null);

        try {
            $this->assertSame(null, $mock->objectReturn());
        } catch (\TypeError $e) {
            $this->fail('Expected stubbing objectReturn null');
        }

        Phake::verify($mock, Phake::times(1))->objectReturn();
    }

    public function testStubbingNullableParameterHints(): void
    {
        $mock = Phake::mock(\PhakeTest_NullableTypes::class);

        try {
            $mock->objectParameter(null);
        } catch (\TypeError $e) {
            $this->fail('Expected stubbing objectParameter to accept null');
        }

        Phake::verify($mock, Phake::times(1))->objectParameter(null);
    }

    public function testStubbingNullableReturnWrongType(): void
    {
        $mock = Phake::mock(\PhakeTest_NullableTypes::class);
        Phake::when($mock)->objectReturn->thenReturn([]);

        try {
            $mock->objectReturn();
        } catch (\TypeError $e) {
            $this->assertTrue(true);

            return;
        } catch (\Throwable $e) {
            $this->fail('Expected A Type Error, instead got ' . get_class($e) . " {$e}");
        }

        $this->fail('Expected A Type Error, no error received');
    }

    public function testDefaultReturnType(): void
    {
        $mock = Phake::mock(\PhakeTest_NullableTypes::class);
        $this->assertTrue($mock->objectReturn() instanceof \PhakeTest_A);
    }

    public function testStubbingUnionTypes(): void
    {
        $this->assertInstanceOf(\PhakeTest_UnionTypes::class, Phake::mock(\PhakeTest_UnionTypes::class));
    }

    public function testStubbingUnionParameterHints(): void
    {
        $mock = Phake::mock(\PhakeTest_UnionTypes::class);

        try {
            $mock->unionParam(1);
            $mock->unionParam('foo');
        } catch (\TypeError $e) {
            $this->fail('Expected stubbing objectParameter to accept 1 and \'foo\'');
        }
        Phake::verify($mock, Phake::times(1))->unionParam(1);
        Phake::verify($mock, Phake::times(1))->unionParam('foo');
    }

    public function testStubbingUnionParameterHintsWrongType(): void
    {
        $mock = Phake::mock(\PhakeTest_UnionTypes::class);

        try {
            $mock->unionParam(null);
        } catch (\TypeError $e) {
            $this->assertTrue(true);

            return;
        }
        $this->fail('Expected TypeError');
    }

    public function testStubbingNullableUnionParameterHints(): void
    {
        $mock = Phake::mock(\PhakeTest_UnionTypes::class);

        try {
            $mock->unionParamNullable(1);
            $mock->unionParamNullable('foo');
            $mock->unionParamNullable(null);
        } catch (\TypeError $e) {
            $this->fail('Expected stubbing objectParameter to accept 1, \'foo\', and null');
        }
        Phake::verify($mock, Phake::times(1))->unionParamNullable(1);
        Phake::verify($mock, Phake::times(1))->unionParamNullable('foo');
        Phake::verify($mock, Phake::times(1))->unionParamNullable(null);
    }

    public function testStubbingUnionReturnType(): void
    {
        $mock = Phake::mock(\PhakeTest_UnionTypes::class);
        Phake::when($mock)->unionReturn()->thenReturn(1)->thenreturn('foo');

        try {
            $this->assertSame(1, $mock->unionReturn());
            $this->assertSame('foo', $mock->unionReturn());
        } catch (\TypeError $e) {
            $this->fail('Expected stubbing return 1 and \'foo\'');
        }
    }

    public function testStubbingUnionReturnWrongType(): void
    {
        $mock = Phake::mock(\PhakeTest_UnionTypes::class);
        Phake::when($mock)->unionReturn()->thenReturn(null);

        try {
            $mock->unionReturn();
        } catch (\TypeError $e) {
            $this->assertTrue(true);

            return;
        }
        $this->fail('Expected stubbing return 1 and \'foo\'');
    }

    public function testStubbingUnionReturnNullableType(): void
    {
        $mock = Phake::mock(\PhakeTest_UnionTypes::class);
        Phake::when($mock)->unionReturnNullable()->thenReturn(1)->thenreturn('foo')->thenReturn(null);

        try {
            $this->assertSame(1, $mock->unionReturnNullable());
            $this->assertSame('foo', $mock->unionReturnNullable());
            $this->assertSame(null, $mock->unionReturnNullable());
        } catch (\TypeError $e) {
            $this->fail('Expected stubbing return 1 and \'foo\'');
        }
    }

    public function testStubbingUnionSelfParameterHints(): void
    {
        $mock = Phake::mock(\PhakeTest_UnionTypes::class);

        try {
            $mock->unionParamWithSelf($mock);
        } catch (\TypeError $e) {
            $this->fail('Expected stubbing parameter to accept self');
        }

        Phake::verify($mock, Phake::times(1))->unionParamWithSelf($mock);
    }

    public function testStubbingUnionReturnWithSelf(): void
    {
        $mock = Phake::mock(\PhakeTest_UnionTypes::class);
        Phake::when($mock)->unionReturnWithSelf()->thenReturnSelf();

        try {
            $this->assertSame($mock, $mock->unionReturnWithSelf());
        } catch (\TypeError $e) {
            $this->fail('Expected stubbing return self');
        }
    }

    public function testStubbingIntersectionTypes(): void
    {
        if (PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('Intersection types are not supported in PHP versions prior to 8.1');
        }
        $this->assertInstanceOf(\PhakeTest_IntersectionTypes::class, Phake::mock(\PhakeTest_IntersectionTypes::class));
    }

    public function testStubbingIntersectionReturnType(): void
    {
        if (PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('Intersection types are not supported in PHP versions prior to 8.1');
        }

        $mock = Phake::mock(\PhakeTest_IntersectionTypes::class);
        $expectedResult = new \ArrayObject();
        Phake::when($mock)->intersectionReturn()->thenReturn($expectedResult);

        try {
            $this->assertSame($expectedResult, $mock->intersectionReturn());
        } catch (\TypeError $e) {
            $this->fail('Expected stubbing return ArrayObject');
        }
    }

    public function testStubbingNeverReturnType(): void
    {
        if (PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('never type is not supported in PHP versions prior to 8.1');
        }
        $this->assertInstanceOf(\PhakeTest_NeverReturn::class, Phake::mock(\PhakeTest_NeverReturn::class));
    }

    public function testStubbingClassWithNewInInitializers(): void
    {
        if (PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('new in initializer is not supported in PHP versions prior to 8.1');
        }
        $this->assertInstanceOf(\PhakeTest_NewInInitializers::class, Phake::mock(\PhakeTest_NewInInitializers::class));
    }

    public function testStubbingDNFTypes(): void
    {
        if (PHP_VERSION_ID < 80200) {
            $this->markTestSkipped('DNF types are not supported in PHP versions prior to 8.2');
        }
        $this->assertInstanceOf(\PhakeTest_DNFTypes::class, Phake::mock(\PhakeTest_DNFTypes::class));
    }

    public function testMockReadonlyClassThrowsException(): void
    {
        if (PHP_VERSION_ID < 80200) {
            $this->markTestSkipped('Readonly classes are not supported in PHP versions prior to 8.2');
        }

        $mock = Phake::mock(\PhakeTest_ReadonlyClass::class);

        $this->assertInstanceOf(\PhakeTest_ReadonlyClass::class, $mock);
        $this->assertInstanceOf(\Phake\IMock::class, $mock);
    }
}
