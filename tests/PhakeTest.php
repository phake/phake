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

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the behavior of the Phake class.
 *
 * The tests below are really all integration tests.
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class PhakeTest extends TestCase
{
    public function setUp(): void
    {
        Phake::setClient(Phake::CLIENT_PHPUNIT8);
    }

    protected function tearDown(): void
    {
        Phake::resetStaticInfo();
        Phake::setClient(Phake::CLIENT_PHPUNIT8);
    }

    /**
     * General test for Phake::mock() that it returns a class that inherits from the passed class.
     */
    public function testMock(): void
    {
        $this->assertThat(Phake::mock(\stdClass::class), $this->isInstanceOf(\stdClass::class));
    }

    /**
     * Tests that a simple method call can be verified
     */
    public function testSimpleVerifyPasses(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->foo();

        Phake::verify($mock)->foo();
    }

    /**
     * Tests that a simple method call verification with throw an exception if that method was not
     * called.
     */
    public function testSimpleVerifyThrowsExceptionOnFail(): void
    {
        $this->expectException(Phake\Exception\VerificationException::class);

        Phake::setClient(Phake::CLIENT_DEFAULT);
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::verify($mock)->foo();
    }

    /**
     * Tests that a simple method call can be stubbed to return an expected value.
     */
    public function testSimpleStub(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::when($mock)->foo()
            ->thenReturn(42);

        $this->assertEquals(42, $mock->foo());
    }

    public function testStaticStub(): void
    {
        $mock = Phake::mock(\PhakeTest_StaticInterface::class);

        Phake::whenStatic($mock)->staticMethod()->thenReturn(42);

        $this->assertEquals(42, $mock::staticMethod());
    }

    /**
     * Tests default parameters
     */
    public function testStubWithDefaultParam(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::when($mock)->fooWithDefault()
            ->thenReturn(42);

        $this->assertEquals(42, $mock->fooWithDefault());
    }

    /**
     * Tests that a stub can be redefined.
     */
    public function testRedefineStub(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::when($mock)->foo()->thenReturn(24);
        Phake::when($mock)->foo()->thenReturn(42);

        $this->assertEquals(42, $mock->foo());
    }

    /**
     * Tests that a stub method can be defined with shorthand notation.
     */
    public function testShorthandVerify(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);
        $mock->foo();
        $mock->foo('bar');

        Phake::verify($mock, Phake::times(2))->foo;
    }

    /**
     * Tests that a stub method can be defined with shorthand notation.
     */
    public function testShorthandStub(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::when($mock)->foo->thenReturn(42);

        $this->assertEquals(42, $mock->foo());
        $this->assertEquals(42, $mock->foo('param'));
    }

    /**
     * Tests that a stub method can be defined with shorthand notation later.
     */
    public function testFirstShorthandStub(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::when($mock)->foo->thenReturn(42);
        Phake::when($mock)->foo('param')->thenReturn(51);

        $this->assertEquals(51, $mock->foo('param'));
        $this->assertEquals(42, $mock->foo());
    }

    /**
     * Tests that a stub method can be redefined with shorthand notation.
     */
    public function testRedefinedShorthandStub(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::when($mock)->foo->thenReturn(42);
        Phake::when($mock)->foo->thenReturn(2);

        $this->assertEquals(2, $mock->foo());
    }

    /**
     * Tests that a stub method can be defined with shorthand notation even with __get().
     */
    public function testMagicClassShorthandStub(): void
    {
        $mock = Phake::mock(\PhakeTest_MagicClass::class);

        Phake::when($mock)->definedMethod->thenReturn(64);
        Phake::when($mock)->__get->thenReturn(75);
        Phake::when($mock)->magicProperty->thenReturn(42);

        $this->assertSame(64, $mock->definedMethod());
        $this->assertSame(75, $mock->otherMagicProperties);
        $this->assertSame(42, $mock->magicProperty);
    }

    /**
     * Tests using multiple stubs.
     */
    public function testMultipleStubs(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::when($mock)->foo()->thenReturn(24);
        Phake::when($mock)->fooWithReturnValue()->thenReturn(42);

        $this->assertEquals(24, $mock->foo());
        $this->assertEquals(42, $mock->fooWithReturnValue());
    }

    /**
     * Tests using multiple stubs.
     */
    public function testConsecutiveCalls(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::when($mock)->foo()->thenReturn(24)->thenReturn(42);

        $this->assertEquals(24, $mock->foo());
        $this->assertEquals(42, $mock->foo());
    }

    /**
     * Tests passing a basic equals matcher to the verify method will correctly verify a call.
     */
    public function testVerifyCallWithEqualsMatcher(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->fooWithArgument('bar');

        Phake::verify($mock)->fooWithArgument(Phake::equalTo('bar'));
    }

    /**
     * Tests passing a basic equals matcher to the verify method will correctly fail when matcher is not satisfied.
     */
    public function testVerifyCallWithEqualsMatcherFails(): void
    {
        $this->expectException(Phake\Exception\VerificationException::class);

        Phake::setClient(Phake::CLIENT_DEFAULT);
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->fooWithArgument('test');

        Phake::verify($mock)->fooWithArgument(Phake::equalTo('bar'));
    }

    /**
     * Tests that we can implicitely indicate an equalTo matcher when we pass in a non-matcher value.
     */
    public function testVerifyCallWithDefaultMatcher(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->fooWithArgument('bar');

        Phake::verify($mock)->fooWithArgument('bar');
    }

    /**
     * Tests passing a default matcher type to the verify method will correctly fail when matcher is not satisfied.
     */
    public function testVerifyCallWithDefaultMatcherFails(): void
    {
        $this->expectException(Phake\Exception\VerificationException::class);

        Phake::setClient(Phake::CLIENT_DEFAULT);
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->fooWithArgument('test');

        Phake::verify($mock)->fooWithArgument('bar');
    }

    /**
     * Tests passing in a PHPUnit constraint to the verifier
     */
    public function testVerifyCallWithPHPUnitMatcher(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->fooWithArgument('bar');

        Phake::verify($mock)->fooWithArgument($this->equalTo('bar'));
    }

    /**
     * Tests passing in a PHPUnit constraint to the verifier fails when constraint not met.
     */
    public function testVerifyCallWithPHPUnitMatcherFails(): void
    {
        $this->expectException(Phake\Exception\VerificationException::class);

        Phake::setClient(Phake::CLIENT_DEFAULT);
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->fooWithArgument('test');

        Phake::verify($mock)->fooWithArgument($this->equalTo('bar'));
    }

    /**
     * Tests passing in a Hamcrest matcher to the verifier
     */
    public function testVerifyCallWithHamcrestMatcher(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->fooWithArgument('bar');

        Phake::verify($mock)->fooWithArgument(equalTo('bar'));
    }

    /**
     * Tests passing in a Hamcrest matcher to the verifier fails when constraint not met.
     */
    public function testVerifyCallWithHamcrestMatcherFails(): void
    {
        $this->expectException(Phake\Exception\VerificationException::class);

        Phake::setClient(Phake::CLIENT_DEFAULT);
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->fooWithArgument('test');

        Phake::verify($mock)->fooWithArgument(equalTo('bar'));
    }

    /**
     * Tests using an equalTo argument matcher with a method stub
     */
    public function testStubWithEqualsMatcher(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::when($mock)->fooWithArgument(Phake::equalTo('bar'))->thenReturn(42);

        $this->assertEquals(42, $mock->fooWithArgument('bar'));
        $this->assertNull($mock->fooWithArgument('test'));
    }

    /**
     * Tests using an implicit equalTo argument matcher with a method stub
     */
    public function testStubWithDefaultMatcher(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::when($mock)->fooWithArgument('bar')->thenReturn(42);

        $this->assertEquals(42, $mock->fooWithArgument('bar'));
        $this->assertNull($mock->fooWithArgument('test'));
    }

    /**
     * Tests using a phpunit constraint with a method stub
     */
    public function testStubWithPHPUnitConstraint(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::when($mock)->fooWithArgument($this->equalTo('bar'))->thenReturn(42);

        $this->assertEquals(42, $mock->fooWithArgument('bar'));
        $this->assertNull($mock->fooWithArgument('test'));
    }

    /**
     * Tests using a hamcrest matcher with a method stub
     */
    public function testStubWithHamcrestConstraint(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::when($mock)->fooWithArgument(equalTo('bar'))->thenReturn(42);

        $this->assertEquals(42, $mock->fooWithArgument('bar'));
        $this->assertNull($mock->fooWithArgument('test'));
    }

    /**
     * Tests that resetting a mock clears the call recorder
     */
    public function testResettingCallRecorder(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->foo();

        Phake::verify($mock)->foo();

        Phake::reset($mock);

        $this->expectException(\Phake\Exception\VerificationException::class);
        Phake::setClient(Phake::CLIENT_DEFAULT);

        Phake::verify($mock)->foo();
    }

    /**
     * Tests that resetting a mock clears the stubber
     */
    public function testResettingStubMapper(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::when($mock)->foo()->thenReturn(42);

        $this->assertEquals(42, $mock->foo());

        Phake::reset($mock);

        $this->assertNull($mock->foo());
    }

    /**
     * Tests that resetting a mock clears the call recorder
     */
    public function testResettingStaticCallRecorder(): void
    {
        $mock = Phake::mock(\PhakeTest_StaticInterface::class);

        $mock::staticMethod();

        Phake::verifyStatic($mock)->staticMethod();

        Phake::resetStatic($mock);

        $this->expectException(\Phake\Exception\VerificationException::class);
        Phake::setClient(Phake::CLIENT_DEFAULT);

        Phake::verifyStatic($mock)->staticMethod();
    }

    public function testMockingPhar(): void
    {
        if (!class_exists('Phar')) {
            $this->markTestSkipped('Phar class does not exist');
        }
        $phar = Phake::mock('Phar');

        $this->assertInstanceOf('Phar', $phar);
    }

    /**
     * Tests that resetting a mock clears the stubber
     */
    public function testResettingStaticStubMapper(): void
    {
        $mock = Phake::mock(\PhakeTest_StaticInterface::class);

        Phake::whenStatic($mock)->staticMethod()->thenReturn(42);

        $this->assertEquals(42, $mock::staticMethod());

        Phake::resetStatic($mock);

        $this->assertNull($mock::staticMethod());
    }

    /**
     * Tests setting a default answer for stubs
     */
    public function testDefaultAnswerForStubs(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class, Phake::ifUnstubbed()->thenReturn(42));

        $this->assertEquals(42, $mock->foo());
    }

    /**
     * Tests setting a default answer for stubs
     */
    public function testDefaultAnswerForInterfaces(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedInterface::class, Phake::ifUnstubbed()->thenReturn(42));

        $this->assertEquals(42, $mock->foo());
    }

    /**
     * Tests setting a default answer for only the __call magic method
     */
    public function testDefaultAnswerForStubsOfCall(): void
    {
        $mock = Phake::mock(\PhakeTest_MagicClass::class);

        Phake::whenCallMethodWith(Phake::anyParameters())->isCalledOn($mock)->thenReturn(42);

        $this->assertEquals(42, $mock->foo());
    }

    /**
     * Tests setting a default answer for only the __call magic method
     */
    public function testDefaultAnswerForStaticStubsOfCall(): void
    {
        $mock = Phake::mock(\PhakeTest_MagicClass::class);

        Phake::whenStaticCallMethodWith(Phake::anyParameters())->isCalledOn($mock)->thenReturn(42);

        $this->assertEquals(42, $mock::foo());
    }

    /**
     * Tests validating calls to __call
     */
    public function testVerificationOfCall(): void
    {
        $mock = Phake::mock(\PhakeTest_MagicClass::class);

        $mock->foo();

        Phake::verifyCallMethodWith(Phake::anyParameters())->isCalledOn($mock);
    }

    /**
     * Tests validating calls to __callStatic
     */
    public function testVerificationOfStaticCall(): void
    {
        $mock = Phake::mock(\PhakeTest_MagicClass::class);

        $mock::foo();

        Phake::verifyStaticCallMethodWith(Phake::anyParameters())->isCalledOn($mock);
    }

    /**
     * Tests stubbing a mocked method to call its parent.
     */
    public function testStubbingMethodToCallParent(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::when($mock)->fooWithReturnValue()->thenCallParent();

        $this->assertEquals('blah', $mock->fooWithReturnValue());
    }

    /**
     * Tests calling through a chain of calls
     */
    public function testStubbingChainedMethodsToCallParent(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class, Phake::ifUnstubbed()->thenCallParent());

        $this->assertEquals('test', $mock->callInnerFunc());
    }

    /**
     * Tests partial mock functionality to make sure original method is called.
     */
    public function testPartialMockCallsOriginal(): void
    {
        $pmock = Phake::partialMock(\PhakeTest_MockedClass::class);
        $this->assertEquals('blah', $pmock->fooWithReturnValue());
    }

    /**
     * Tests partial mock calls are recorded
     */
    public function testPartialMockRecordsCall(): void
    {
        $pmock = Phake::partialMock(\PhakeTest_MockedClass::class);
        $pmock->foo();

        Phake::verify($pmock)->foo();
    }

    /**
     * Tests that partial mock calls can chain properly
     */
    public function testPartialMockInternalMethodCalls(): void
    {
        $pmock = Phake::partialMock(\PhakeTest_MockedClass::class);
        Phake::when($pmock)->innerFunc()->thenReturn('blah');

        $this->assertEquals('blah', $pmock->chainedCall());
    }

    /**
     * Tests that partial mock can overwrite methods
     * so that they don't do anything when they get called
     */
    public function testPartialMockCanReturnNothing(): void
    {
        $pmock = Phake::partialMock(\PhakeTest_MockedClass::class);
        Phake::when($pmock)->innerFunc()->thenDoNothing();

        $this->assertNull($pmock->chainedCall());
    }

    /**
     * Tests that partial mocks listen to the constructor args given
     */
    public function testPartialMockCallsConstructor(): void
    {
        $pmock = Phake::partialMock(\PhakeTest_MockedConstructedClass::class, 'val1', 'val2', 'val3');

        $this->assertEquals('val1', $pmock->getProp1());
        $this->assertEquals('val2', $pmock->getProp2());
        $this->assertEquals('val3', $pmock->getProp3());
    }

    /**
     * Tests that partial mocks with constructors higher in the chain have their constructors called
     */
    public function testPartialMockCallsParentConstructor(): void
    {
        $pmock = Phake::partialMock(\PhakeTest_ExtendedMockedConstructedClass::class, 'val1', 'val2', 'val3');

        $this->assertEquals('val1', $pmock->getProp1());
        $this->assertEquals('val2', $pmock->getProp2());
        $this->assertEquals('val3', $pmock->getProp3());
    }

    /**
     * Tests mocking of an interface
     */
    public function testMockingInterface(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedInterface::class);

        Phake::when($mock)->foo()->thenReturn('bar');

        $this->assertEquals('bar', $mock->foo());
    }

    /**
     * Tests mocking of an abstract class
     */
    public function testMockingAbstract(): void
    {
        $mock = Phake::mock(\PhakeTest_AbstractClass::class);

        Phake::when($mock)->foo()->thenReturn('bar');

        $this->assertEquals('bar', $mock->foo());
    }

    /**
     * Tests verifying the call order of particular methods within an object
     */
    public function testCallOrderInObject(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->foo();
        $mock->fooWithReturnValue();
        $mock->callInnerFunc();

        Phake::inOrder(
            Phake::verify($mock)->foo(),
            Phake::verify($mock)->fooWithReturnValue(),
            Phake::verify($mock)->callInnerFunc()
        );
    }

    /**
     * Tests verifying the call order of particular methods within an object
     */
    public function testCallOrderInObjectFails(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->foo();
        $mock->callInnerFunc();
        $mock->fooWithReturnValue();

        $this->expectException(\Phake\Exception\VerificationException::class);
        Phake::setClient(Phake::CLIENT_DEFAULT);

        Phake::inOrder(
            Phake::verify($mock)->foo(),
            Phake::verify($mock)->fooWithReturnValue(),
            Phake::verify($mock)->callInnerFunc()
        );
    }

    /**
     * Tests verifying the call order of particular methods across objects
     */
    public function testCallOrderAccrossObjects(): void
    {
        $mock1 = Phake::mock(\PhakeTest_MockedClass::class);
        $mock2 = Phake::mock(\PhakeTest_MockedClass::class);

        $mock1->foo();
        $mock2->foo();
        $mock1->fooWithReturnValue();
        $mock2->fooWithReturnValue();
        $mock1->callInnerFunc();
        $mock2->callInnerFunc();

        Phake::inOrder(
            Phake::verify($mock1)->foo(),
            Phake::verify($mock2)->foo(),
            Phake::verify($mock2)->fooWithReturnValue(),
            Phake::verify($mock1)->callInnerFunc()
        );
    }

    /**
     * Tests verifying the call order of particular methods across objects
     */
    public function testCallOrderAccrossObjectsFail(): void
    {
        $mock1 = Phake::mock(\PhakeTest_MockedClass::class);
        $mock2 = Phake::mock(\PhakeTest_MockedClass::class);

        $mock1->foo();
        $mock2->foo();
        $mock1->fooWithReturnValue();
        $mock1->callInnerFunc();
        $mock2->fooWithReturnValue();
        $mock2->callInnerFunc();

        $this->expectException(\Phake\Exception\VerificationException::class);
        Phake::setClient(Phake::CLIENT_DEFAULT);

        Phake::inOrder(
            Phake::verify($mock2)->fooWithReturnValue(),
            Phake::verify($mock1)->callInnerFunc()
        );
    }

    public function testCallOrderWithStatics(): void
    {
        $mock1 = Phake::mock(\PhakeTest_MockedClass::class);
        $mock2 = Phake::mock(\PhakeTest_StaticInterface::class);

        $mock1->foo();
        $mock2::staticMethod();
        $mock1->fooWithReturnValue();
        $mock1->callInnerFunc();

        Phake::inOrder(
            Phake::verify($mock1)->foo(),
            Phake::verifyStatic($mock2)->staticMethod(),
            Phake::verify($mock1)->callInnerFunc()
        );
    }

    /**
     * Tests freezing mocks
     */
    public function testMockFreezing(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->foo();

        Phake::verifyNoFurtherInteraction($mock);

        $this->expectException(\Phake\Exception\VerificationException::class);
        Phake::setClient(Phake::CLIENT_DEFAULT);

        $mock->foo();
    }

    public function testStaticMockFreezing(): void
    {
        $mock = Phake::mock(\PhakeTest_StaticInterface::class);

        $mock::staticMethod();

        Phake::verifyNoFurtherInteraction($mock);

        $this->expectException(\Phake\Exception\VerificationException::class);
        Phake::setClient(Phake::CLIENT_DEFAULT);

        $mock::staticMethod();
    }

    /**
     * Tests freezing mocks
     */
    public function testMockFreezingWithMultipleMocks(): void
    {
        $mock1 = Phake::mock(\PhakeTest_MockedClass::class);
        $mock2 = Phake::mock(\PhakeTest_MockedClass::class);

        $mock1->foo();
        $mock2->foo();

        Phake::verifyNoFurtherInteraction($mock1, $mock2);

        $this->expectException(\Phake\Exception\VerificationException::class);
        Phake::setClient(Phake::CLIENT_DEFAULT);

        $mock2->foo();
    }

    /**
     * Tests verifying that no interaction occured
     */
    public function testVerifyingZeroInteraction(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::verifyNoInteraction($mock);

        $mock->foo();

        $this->expectException(\Phake\Exception\VerificationException::class);
        Phake::setClient(Phake::CLIENT_DEFAULT);
        Phake::verifyNoInteraction($mock);
    }

    /**
     * Tests verifying that no interaction occured
     */
    public function testVerifyingZeroInteractionIncludesStatic(): void
    {
        $mock = Phake::mock(\PhakeTest_StaticInterface::class);

        Phake::verifyNoInteraction($mock);

        $mock::staticMethod();

        $this->expectException(\Phake\Exception\VerificationException::class);
        Phake::setClient(Phake::CLIENT_DEFAULT);
        Phake::verifyNoInteraction($mock);
    }

    /**
     * Tests verifying that no interaction occured
     */
    public function testVerifyingZeroInteractionWithMultipleArgs(): void
    {
        $mock1 = Phake::mock(\PhakeTest_MockedClass::class);
        $mock2 = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::verifyNoInteraction($mock1, $mock2);

        $mock2->foo();

        $this->expectException(\Phake\Exception\VerificationException::class);
        Phake::setClient(Phake::CLIENT_DEFAULT);
        Phake::verifyNoInteraction($mock1, $mock2);
    }

    /**
     * Tests argument capturing
     */
    public function testArugmentCapturing(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->fooWithArgument('TEST');

        Phake::verify($mock)->fooWithArgument(Phake::capture($toArgument));

        $this->assertSame('TEST', $toArgument);
    }

    /**
     * Tests conditional argument capturing
     */
    public function testConditionalArugmentCapturing(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->fooWithArgument('FOO');

        $mock->fooWithArgument('BAR');


        Phake::verify($mock)->fooWithArgument(Phake::capture($toArgument)->when('BAR'));

        $this->assertSame('BAR', $toArgument);
    }

    /**
     * Make sure arguments aren't captured if the conditions don't match
     */
    public function testConditionalArugmentCapturingFails(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->fooWithArgument('FOO');

        $this->expectException(\Phake\Exception\VerificationException::class);
        Phake::setClient(Phake::CLIENT_DEFAULT);
        Phake::verify($mock)->fooWithArgument(Phake::capture($toArgument)->when('BAR'));
    }

    /**
     * Make sure arguments are captured with no issues
     */
    public function testArgumentCapturingWorksOnObjects(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $obj = new stdClass();

        $mock->fooWithArgument($obj);

        Phake::verify($mock)->fooWithArgument(Phake::capture($toArgument));

        $this->assertSame($obj, $toArgument);
    }

    /**
     * Make sure arguments are captured with no issues
     */
    public function testArgumentCapturingWorksOnStubbing(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $obj = new stdClass();

        Phake::when($mock)->fooWithArgument(Phake::capture($toArgument))->thenReturn(true);

        $mock->fooWithArgument($obj);

        $this->assertSame($obj, $toArgument);
    }

    public function testArgumentCapturingAllValls(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $obj1 = new stdClass();
        $obj2 = new stdClass();
        $obj3 = new stdClass();

        $mock->fooWithArgument($obj1);
        $mock->fooWithArgument($obj2);
        $mock->fooWithArgument($obj3);

        Phake::verify($mock, Phake::atLeast(1))->fooWithArgument(Phake::captureAll($toArgument));

        $this->assertSame([$obj1, $obj2, $obj3], $toArgument);
    }

    /**
     * Make sure stub return value capturing returns the parent value
     */
    public function testCaptureAnswerReturnsParentValue(): void
    {
        $return = null;
        $mock = Phake::mock(\PhakeTest_MockedClass::class);
        Phake::when($mock)->fooWithReturnValue()->captureReturnTo($return);

        $this->assertEquals('blah', $mock->fooWithReturnValue());
    }

    /**
     * Make sure stub return value capturing returns the parent value
     */
    public function testCaptureAnswerCapturesParentValue(): void
    {
        $return = null;
        $mock = Phake::mock(\PhakeTest_MockedClass::class);
        Phake::when($mock)->fooWithReturnValue()->captureReturnTo($return);

        $mock->fooWithReturnValue();

        $this->assertEquals('blah', $return);
    }

    /**
     * Tests setting reference parameters
     */
    public function testSettingReferenceParameters(): void
    {
        $value = null;
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::when($mock)->fooWithRefParm('test', Phake::setReference(42))->thenReturn(null);

        $mock->fooWithRefParm('test', $value);

        $this->assertSame(42, $value);
    }

    /**
     * Tests conditional reference parameter setting
     */
    public function testConditionalReferenceParameterSetting(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::when($mock)->fooWithRefParm('test', Phake::setReference(42)->when(24))->thenReturn(null);

        $value = 24;
        $mock->fooWithRefParm('test', $value);

        $this->assertSame(42, $value);
    }

    /**
     * Make sure reference parameters aren't set if the conditions don't match
     */
    public function testConditionalReferenceParameterSettingFails(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::when($mock)->fooWithRefParm('test', Phake::setReference(42)->when(24))->thenReturn(null);

        $value = 25;
        $mock->fooWithRefParm('test', $value);

        $this->assertSame(25, $value);
    }

    /**
     * Make sure paremeters are set to objects with no issues
     */
    public function testReferenceParameterSettingWorksOnObjects(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $obj = new stdClass();
        Phake::when($mock)->fooWithRefParm('test', Phake::setReference($obj))->thenReturn(null);

        $value = 25;
        $mock->fooWithRefParm('test', $value);

        $this->assertSame($obj, $value);
    }

    /**
     * Tests times matches exactly
     */
    public function testVerifyTimesExact(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->foo();
        $mock->foo();

        Phake::verify($mock, Phake::times(2))->foo();
    }

    /**
     * Tests times doesn't match
     */
    public function testVerifyTimesMismatch(): void
    {
        $this->expectException(\Phake\Exception\VerificationException::class);

        Phake::setClient(Phake::CLIENT_DEFAULT);
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->foo();
        $mock->foo();

        Phake::verify($mock)->foo();
    }

    /**
     * Tests at least matches with exact calls
     */
    public function testVerifyAtLeastExact(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->foo();

        Phake::verify($mock, Phake::atLeast(1))->foo();
    }

    /**
     * Tests at least matches with greater calls
     */
    public function testVerifyAtLeastGreater(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->foo();
        $mock->foo();

        Phake::verify($mock, Phake::atLeast(1))->foo();
    }

    /**
     * Tests that at least doesn't match
     */
    public function testVerifyAtLeastMismatch(): void
    {
        $this->expectException(\Phake\Exception\VerificationException::class);

        Phake::setClient(Phake::CLIENT_DEFAULT);
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::verify($mock, Phake::atLeast(1))->foo();
    }

    /**
     * Tests that never matches
     */
    public function testNeverMatches(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);
        Phake::verify($mock, Phake::never())->foo();
    }

    /**
     * Tests that never catches an invocation
     */
    public function testNeverMismatch(): void
    {
        $this->expectException(Phake\Exception\VerificationException::class);

        Phake::setClient(Phake::CLIENT_DEFAULT);
        $mock = Phake::mock(\PhakeTest_MockedClass::class);
        $mock->foo();
        Phake::verify($mock, Phake::never())->foo();
    }

    /**
     * Tests that atMost passes with exact
     */
    public function testAtMostExactly(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);
        $mock->foo();
        Phake::verify($mock, Phake::atMost(1))->foo();
    }

    /**
     * Tests that atMost passes with under expected calls
     */
    public function testAtMostUnder(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);
        Phake::verify($mock, Phake::atMost(1))->foo();
    }

    /**
     * Tests that atMost fails on over calls
     */
    public function testAtMostOver(): void
    {
        $this->expectException(Phake\Exception\VerificationException::class);

        Phake::setClient(Phake::CLIENT_DEFAULT);
        $mock = Phake::mock(\PhakeTest_MockedClass::class);
        $mock->foo();
        $mock->foo();
        Phake::verify($mock, Phake::atMost(1))->foo();
    }

    /**
     * Tests that the given exception is thrown on thenThrow.
     */
    public function testStubThenThrow(): void
    {
        $this->expectException(Phake\Exception\VerificationException::class);

        $mock = Phake::mock(\PhakeTest_MockedClass::class);
        Phake::when($mock)->foo()->thenThrow(new Phake\Exception\VerificationException());
        $mock->foo();
    }

    /**
     * Tests that the thenThrow also now supports throwable
     */
    public function testStubThenThrowWithThrowable(): void
    {
        $this->expectException(\AssertionError::class);

        $mock = Phake::mock(\PhakeTest_MockedClass::class);
        Phake::when($mock)->foo()->thenThrow(new \AssertionError());
        $mock->foo();
    }

    /**
     * Tests that Phake::anyParameters() returns an instance of Phake\Matchers\AnyParameters
     */
    public function testAnyParameters(): void
    {
        $matcher = Phake::anyParameters();

        $this->assertInstanceOf(\Phake\Matchers\AnyParameters::class, $matcher);
    }

    /**
     * Tests that Phake::anyParameters() really matches any invocation
     */
    public function testAnyParametersMatchesEverything(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->fooWithLotsOfParameters(1, 2, 3);
        $mock->fooWithLotsOfParameters(1, 3, 2);
        $mock->fooWithLotsOfParameters(2, 1, 3);
        $mock->fooWithLotsOfParameters(2, 3, 1);
        $mock->fooWithLotsOfParameters(3, 1, 2);
        $mock->fooWithLotsOfParameters(3, 2, 1);

        Phake::verify($mock, Phake::times(6))->fooWithLotsOfParameters(Phake::anyParameters());
    }

    public function testAnyParametersThrowsAnErrorWithTrailingParameters(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->fooWithLotsOfParameters(3, 2, 1);

        $this->expectException(\InvalidArgumentException::class);
        Phake::verify($mock)->fooWithLotsOfParameters(Phake::anyParameters(), 1);
    }

    public function testAnyParametersThrowsAnErrorWithPrecedingParameters(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->fooWithLotsOfParameters(3, 2, 1);

        $this->expectException(\InvalidArgumentException::class);
        Phake::verify($mock)->fooWithLotsOfParameters(3, Phake::anyParameters());
    }

    public function testIgnoreRemainingMatchesEverything(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->fooWithLotsOfParameters(1, 2, 3);
        $mock->fooWithLotsOfParameters(1, 3, 2);
        $mock->fooWithLotsOfParameters(1, 1, 3);
        $mock->fooWithLotsOfParameters(1, 3, 1);
        $mock->fooWithLotsOfParameters(1, 1, 2);
        $mock->fooWithLotsOfParameters(1, 2, 1);

        Phake::verify($mock, Phake::times(6))->fooWithLotsOfParameters(1, Phake::ignoreRemaining());
    }

    public function testIgnoreRemainingThrowsAnErrorWithTrailingParameters(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->fooWithLotsOfParameters(3, 2, 1);

        $this->expectException(\InvalidArgumentException::class);
        Phake::verify($mock)->fooWithLotsOfParameters(Phake::ignoreRemaining(), 1);
    }

    /**
     * Tests that when stubs are defined, they're matched in reverse order.
     */
    public function testMatchesInReverseOrder(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::when($mock)->fooWithArgument($this->anything())->thenReturn(false);
        Phake::when($mock)->fooWithArgument('foo')->thenReturn(true);

        $this->assertTrue($mock->fooWithArgument('foo'));
    }

    public function testFailedVerificationWithNoMockInteractions(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $this->expectException(
            \Phake\Exception\VerificationException::class
        );
        Phake::setClient(Phake::CLIENT_DEFAULT);
        Phake::verify($mock)->foo();
    }

    public function testFailedVerificationWithNonmatchingMethodCalls(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->foo('test');

        Phake::setClient(Phake::CLIENT_DEFAULT);
        $this->expectException(
            \Phake\Exception\VerificationException::class
        );

        Phake::verify($mock)->foo();
    }

    public function testStubbingMagicCallMethod(): void
    {
        $mock = Phake::mock(\PhakeTest_MagicClass::class);

        Phake::when($mock)->magicCall()->thenReturn('magicCalled');

        $this->assertEquals('magicCalled', $mock->magicCall());
    }

    public function testVerifyingMagicCallMethod(): void
    {
        $mock = Phake::mock(\PhakeTest_MagicClass::class);

        $mock->magicCall();

        Phake::verify($mock)->magicCall();
    }

    public function testStubbingMagicMethodsAlsoResortsToCallIfNoStubsDefined(): void
    {
        $expected = '__call';
        $mock     = Phake::partialMock(\PhakeTest_MagicClass::class);

        Phake::when($mock)->magicCall()->thenReturn('magicCalled');

        $this->assertEquals('magicCalled', $mock->magicCall());
        $this->assertEquals($expected, $mock->unStubbedCall());
    }

    public function testStubbingMagicStaticCallMethod(): void
    {
        $mock = Phake::mock(\PhakeTest_MagicClass::class);

        Phake::whenStatic($mock)->magicCall()->thenReturn('magicCalled');

        $this->assertEquals('magicCalled', $mock::magicCall());
    }

    public function testMockingSoapClient(): void
    {
        if (!extension_loaded('soap')) {
            $this->markTestSkipped('Soap extension not loaded');
        }
        // This test requires that E_STRICT be on
        // It will fail with it on, otherwise it wont' complain
        $mock = Phake::mock('SoapClient');

        $this->addToAssertionCount(1);
    }

    public function testDefaultClient(): void
    {
        $original_client = Phake::getClient();

        Phake::setClient(null);

        $this->assertInstanceOf(\Phake\Client\DefaultClient::class, Phake::getClient());

        Phake::setClient($original_client);
    }

    public function testSettingClient(): void
    {
        $original_client = Phake::getClient();

        $client = Phake::mock(\Phake\Client\IClient::class);
        Phake::setClient($client);

        $this->assertSame($client, Phake::getClient());

        Phake::setClient($original_client);
    }

    public function testSettingDefaultClientByString(): void
    {
        $original_client = Phake::getClient();

        Phake::setClient(Phake::CLIENT_DEFAULT);

        $this->assertInstanceOf(\Phake\Client\DefaultClient::class, Phake::getClient());

        Phake::setClient($original_client);
    }

    public function testSettingPHPUnitClientByString(): void
    {
        $original_client = Phake::getClient();

        Phake::setClient(Phake::CLIENT_PHPUNIT8);

        $this->assertInstanceOf(\Phake\Client\PHPUnit8::class, Phake::getClient());

        Phake::setClient($original_client);
    }

    public function testVerifyNoFurtherInteractionPassesStrict(): void
    {
        Phake::setClient(Phake::CLIENT_PHPUNIT8);
        $mock = Phake::mock(\stdClass::class);

        $assertionCount = self::getCount();
        Phake::verifyNoFurtherInteraction($mock);
        $newAssertionCount = self::getCount();

        $this->assertGreaterThan($assertionCount, $newAssertionCount);
    }

    public function testVerifyNoInteractionPassesStrict(): void
    {
        Phake::setClient(Phake::CLIENT_PHPUNIT8);
        $mock = Phake::mock(\stdClass::class);

        $assertionCount = self::getCount();
        Phake::verifyNoInteraction($mock);
        $newAssertionCount = self::getCount();

        $this->assertGreaterThan($assertionCount, $newAssertionCount);
    }

    public function testMockingStaticClass(): void
    {
        $mock = Phake::mock(\PhakeTest_StaticClass::class);

        Phake::whenStatic($mock)->staticMethod()->thenReturn('bar');

        $this->assertEquals('bar', $mock->staticMethod());
        Phake::verifyStatic($mock)->staticMethod();
    }

    public function testMockingStaticInterface(): void
    {
        $mock = Phake::mock(\PhakeTest_StaticInterface::class);

        $this->assertInstanceOf(\Phake\IMock::class, $mock);
    }

    public function testCallingMockStaticMethod(): void
    {
        $mock = Phake::mock(\PhakeTest_StaticInterface::class);

        $this->assertNull($mock::staticMethod());
    }

    public function testVerifyingMockStaticMethod(): void
    {
        $mock = Phake::mock(\PhakeTest_StaticInterface::class);

        $mock::staticMethod();

        Phake::verifyStatic($mock)->staticMethod();
    }

    public function testMockingAbstractClass(): void
    {
        $mock = Phake::partialMock(\PhakeTest_AbstractClass::class);
        $this->assertNull($mock->referenceDefault());
    }

    public function testStubbingMemcacheSetMethod(): void
    {
        if (!extension_loaded('memcache')) {
            $this->markTestSkipped('memcache extension not loaded');
        }

        $memcache = Phake::mock(\Memcache::class);

        Phake::when($memcache)->set('key', 'value')->thenReturn(true);

        $this->assertTrue($memcache->set('key', 'value'));
    }

    public function testMockingMethodReturnByReference(): void
    {
        $something            = [];
        $referenceMethodClass = Phake::mock(\PhakeTest_ReturnByReferenceMethodClass::class);

        Phake::when($referenceMethodClass)->getSomething()->thenReturn($something);

        $something[]     = 'foo';
        $returnSomething = $referenceMethodClass->getSomething();

        $this->assertNotContains('foo', $returnSomething);
    }

    public function testGetOnMockedClass(): void
    {
        $mock = Phake::mock(\PhakeTest_MagicClass::class);
        Phake::when($mock)->__get('myId')->thenReturn(500)->thenReturn(501);

        $this->assertEquals(500, $mock->myId);
        $this->assertEquals(501, $mock->myId);

        Phake::verify($mock, Phake::times(2))->__get('myId');
    }

    public function testCallOrderInObjectFailsWithPHPUnit(): void
    {
        Phake::setClient(Phake::CLIENT_PHPUNIT8);

        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        $mock->foo();
        $mock->callInnerFunc();
        $mock->fooWithReturnValue();

        $this->expectException(ExpectationFailedException::class);

        Phake::inOrder(
            Phake::verify($mock)->foo(),
            Phake::verify($mock)->fooWithReturnValue(),
            Phake::verify($mock)->callInnerFunc()
        );
    }

    public function testGetMockedClassAnythingMatcher(): void
    {
        $mock = Phake::mock(\PhakeTest_MagicClass::class);

        Phake::when($mock)->__get($this->anything())->thenReturn(500);

        $this->assertEquals(500, $mock->myId);

        Phake::verify($mock)->__get($this->anything());
    }

    public function testConstructorInterfaceCanBeMocked(): void
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('This test causes a fatal error under HHVM.');
        }

        // Generated a fatal error before fixed
        $this->assertInstanceOf(\Phake\IMock::class, Phake::mock(\PhakeTest_ConstructorInterface::class));
    }

    public function testClassWithWakeupWorks(): void
    {
        $this->assertInstanceOf(\Phake\IMock::class, Phake::mock(\PhakeTest_WakeupClass::class));
    }

    public function testMockPDOStatement(): void
    {
        $this->assertInstanceOf(\PDOStatement::class, Phake::mock(\PDOStatement::class));
    }

    public function testMocksNotEqual(): void
    {
        $chocolateCookie = Phake::mock(\PhakeTest_A::class);
        $berryCookie = Phake::mock(\PhakeTest_A::class);

        $this->assertNotSame($chocolateCookie, $berryCookie);
    }

    public function testStaticClassesReset(): void
    {
        $mock1 = Phake::mock(\PhakeTest_StaticInterface::class);
        $mock1::staticMethod();
        Phake::verifyStatic($mock1)->staticMethod();

        Phake::resetStaticInfo();

        $mock2 = Phake::mock(\PhakeTest_StaticInterface::class);
        $mock2::staticMethod();
        Phake::verifyStatic($mock2)->staticMethod();
    }

    public function testMockPDO(): void
    {
        $this->assertInstanceOf(\PDO::class, Phake::mock(\PDO::class));
    }

    public function testMockPDOExtendingStatementClass(): void
    {
        $this->assertInstanceOf(
            \PhakeTest_PDOStatementExtendingClass::class,
            Phake::mock(\PhakeTest_PDOStatementExtendingClass::class)
        );
    }

    public function testMockPDOExtendingClass(): void
    {
        $this->assertInstanceOf(
            \PhakeTest_PDOExtendingClass::class,
            Phake::mock(\PhakeTest_PDOExtendingClass::class)
        );
    }

    public function testMockRedis(): void
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('Cannot run this test without mock redis');
        }

        $mock = Phake::mock(\Redis::class);
        $this->assertInstanceOf(\Redis::class, $mock);
    }

    public function testFinallyBlockFiresVerifications(): void
    {
        eval('
            $this->expectException("InvalidArgumentException");
            $mock = Phake::mock("PhakeTest_MockedClass");
            try
            {
                $mock->foo();
                throw new InvalidArgumentException();
            }
            finally
            {
                Phake::verify($mock)->foo();
            }
        ');
    }

    public function testVerifyNoOtherInteractions(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);
        $mock->foo('a');
        $mock->foo('b');

        Phake::verify($mock)->foo('a');
        $this->expectException(\Phake\Exception\VerificationException::class);
        Phake::setClient(Phake::CLIENT_DEFAULT);
        Phake::verifyNoOtherInteractions($mock);
    }

    public function testVerifyNoOtherInteractionsWorks(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);
        $mock->foo('a');
        $mock->foo('b');

        Phake::verify($mock)->foo('a');
        Phake::verify($mock)->foo('b');
        Phake::verifyNoOtherInteractions($mock);
    }

    public static function provideVariadicVerifyNoOtherInteractionsWorks(): Generator
    {
        yield 'Single mock, no call' => [[[]], [[]]];
        yield 'Single mock, single call, all verified' => [[['a']], [['a']]];
        yield 'Single mock, single call, none verified' => [[['a']], [[]]];
        yield 'Single mock, multiple calls, all verified' => [[['a', 'b', 'c']], [['a', 'b', 'c']]];
        yield 'Single mock, multiple calls, only first is verified' => [[['a', 'b', 'c']], [['a']]];
        yield 'Single mock, multiple calls, only one is verified' => [[['a', 'b', 'c']], [['b']]];
        yield 'Single mock, multiple calls, only last is verified' => [[['a', 'b', 'c']], [['c']]];
        yield 'Single mock, multiple calls, none verified' => [[['a', 'b', 'c']], [[]]];
        yield 'Two mocks, no calls' => [[[], []], [[], []]];
        yield 'Two mocks, only first does single call, all verified' => [[['a'], []], [['a'], []]];
        yield 'Two mocks, only first does single call, none verified' => [[['a'], []], [[], []]];
        yield 'Two mocks, only first does multiple calls, all verified' => [
            [['a', 'b', 'c'], []],
            [['a', 'b', 'c'], []],
        ];
        yield 'Two mocks, only first does multiple calls, only first is verified' => [
            [['a', 'b', 'c'], []],
            [['a'], []],
        ];
        yield 'Two mocks, only first does multiple calls, only one is verified' => [
            [['a', 'b', 'c'], []],
            [['b'], []],
        ];
        yield 'Two mocks, only first does multiple calls, only last is verified' => [
            [['a', 'b', 'c'], []],
            [['c'], []],
        ];
        yield 'Two mocks, only first does multiple calls, none verified' => [
            [['a', 'b', 'c'], []],
            [[], []],
        ];
        yield 'Two mocks, only last does single call, all verified' => [
            [[], ['x']],
            [[], ['x']],
        ];
        yield 'Two mocks, only last does single call, none verified' => [
            [[], ['x']],
            [[], []],
        ];
        yield 'Two mocks, only last does multiple calls, all verified' => [
            [[], ['x', 'y', 'z']],
            [[], ['x', 'y', 'z']],
        ];
        yield 'Two mocks, only last does multiple calls, only first is verified' => [
            [[], ['x', 'y', 'z']],
            [[], ['x']],
        ];
        yield 'Two mocks, only last does multiple calls, only one is verified' => [
            [[], ['x', 'y', 'z']],
            [[], ['y']],
        ];
        yield 'Two mocks, only last does multiple calls, only last is verified' => [
            [[], ['x', 'y', 'z']],
            [[], ['z']],
        ];
        yield 'Two mocks, only last does multiple calls, none verified' => [
            [[], ['x', 'y', 'z']],
            [[], []],
        ];
        yield 'Two mocks, both do simple call, all verified' => [
            [['a'], ['x']],
            [['a'], ['x']],
        ];
        yield 'Two mocks, both do simple call, only call of first mock is verified' => [
            [['a'], ['x']],
            [['a'], []],
        ];
        yield 'Two mocks, both do simple call, only call of last mock is verified' => [
            [['a'], ['x']],
            [[], ['x']],
        ];
        yield 'Two mocks, both do simple call, none verified' => [
            [['a'], ['x']],
            [[], []],
        ];
        yield 'Two mocks, both do multiple calls, all verified' => [
            [['a', 'b', 'c'], ['x', 'y', 'z']],
            [['a', 'b', 'c'], ['x', 'y', 'z']],
        ];
        yield 'Two mocks, both do multiple calls, only calls of first mock are verified' => [
            [['a', 'b', 'c'], ['x', 'y', 'z']],
            [['a', 'b', 'c'], []],
        ];
        yield 'Two mocks, both do multiple calls, only one call of each mock is verified' => [
            [['a', 'b', 'c'], ['x', 'y', 'z']],
            [['b'], ['y']],
        ];
        yield 'Two mocks, both do multiple calls, only calls of last mock are verified' => [
            [['a', 'b', 'c'], ['x', 'y', 'z']],
            [[], ['x', 'y', 'z']],
        ];
        yield 'Two mocks, both do multiple calls, none verified' => [
            [['a', 'b', 'c'], ['x', 'y', 'z']],
            [[], []],
        ];
    }

    /**
     * @dataProvider provideVariadicVerifyNoOtherInteractionsWorks
     * @param array<array<string>> $calls List of calls for each mock
     * @param array<array<string> $verifications List of calls that will be verified for each mock
     */
    public function testVariadicVerifyNoOtherInteractionsWorks(array $calls, array $verifications): void
    {
        $mocks = array_fill(0, count($calls), Phake::mock(\PhakeTest_MockedClass::class));
        array_map(static function (\Phake\IMock $mock, array $calls) {
            array_map([$mock, 'foo'], $calls);
        }, $mocks, $calls);

        array_map(static function (\Phake\IMock $mock, array $verifications) {
            array_map([Phake::verify($mock), 'foo'], $verifications);
        }, $mocks, $verifications);

        if ($calls !== $verifications) {
            $this->expectException(\Phake\Exception\VerificationException::class);
            Phake::setClient(Phake::CLIENT_DEFAULT);
        }
        Phake::verifyNoOtherInteractions(...$mocks);
    }

    public function testCallingProtectedMethods(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);
        Phake::when($mock)->innerFunc()->thenCallParent();

        $returned = Phake::makeVisible($mock)->innerFunc();

        Phake::verify($mock)->innerFunc();
        $this->assertSame('test', $returned);
    }

    public function testCallingPrivateMethods(): void
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped("Can't call private methods with hhvm");
        }
        $mock = Phake::mock(\PhakeTest_MockedClass::class);
        Phake::when($mock)->privateFunc()->thenCallParent();

        $returned = Phake::makeVisible($mock)->privateFunc();

        $this->assertSame('blah', $returned);
    }

    public function testCallingProtectedStaticMethods(): void
    {
        $mock = Phake::mock(\PhakeTest_StaticClass::class);
        Phake::whenStatic($mock)->protectedStaticMethod()->thenCallParent();

        $returned = Phake::makeStaticsVisible($mock)->protectedStaticMethod();

        Phake::verifyStatic($mock)->protectedStaticMethod();
        $this->assertSame('foo', $returned);
    }

    public function testThenReturnCallback(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);

        Phake::when($mock)->foo->thenReturnCallback(function () {
            return true;
        });

        $this->assertTrue($mock->foo());
    }

    public function testMockingMultipleInterfaces(): void
    {
        $mock = Phake::mock([\PhakeTest_MockedInterface::class, \PhakeTest_MockedClass::class]);
        $this->assertInstanceOf(\PhakeTest_MockedInterface::class, $mock);
        $this->assertInstanceOf(\PhakeTest_MockedClass::class, $mock);

        Phake::when($mock)->foo->thenReturn('bar');
        Phake::when($mock)->reference->thenReturn('foo');
        Phake::when($mock)->fooWithArgument->thenReturn(42);

        $this->assertEquals('bar', $mock->foo());
        $this->assertEquals('foo', $mock->reference($test));
        $this->assertEquals(42, $mock->fooWithArgument('blah'));

        Phake::verify($mock)->foo();
        Phake::verify($mock)->reference(null);
        Phake::verify($mock)->fooWithArgument('blah');
    }

    public function testReturningSelf(): void
    {
        $mock = Phake::mock(\PhakeTest_MockedClass::class);
        Phake::when($mock)->foo->thenReturnSelf();

        $this->assertSame($mock, $mock->foo());
    }

    public function testResetStaticPostCall(): void
    {
        $obj = new PhakeTest_StaticMethod();
        $obj->className = Phake::mock(\PhakeTest_ClassWithStaticMethod::class);
        Phake::whenStatic($obj->className)->ask()->thenReturn('ASKED');

        $val = $obj->askSomething();
        Phake::verifyStatic($obj->className)->ask();

        $this->assertEquals('ASKED', $val);

        $obj->className = Phake::resetStatic($obj->className);

        $val = $obj->askSomething();
        $this->assertEquals('Asked', $val);
    }

    public function testCallingNeverReturnMockedMethodThrowsNeverReturnMethodCalledException(): void
    {
        if (PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('never type is not supported in PHP versions prior to 8.1');
        }

        $this->expectException(Phake\Exception\NeverReturnMethodCalledException::class);

        $mock = Phake::mock(\PhakeTest_NeverReturn::class);
        $mock->neverReturn();
    }

    public function testCallingNeverReturnMockedMethodWithThenThrows(): void
    {
        if (PHP_VERSION_ID < 80100) {
            $this->markTestSkipped('never type is not supported in PHP versions prior to 8.1');
        }

        $mock = Phake::mock(\PhakeTest_NeverReturn::class);
        Phake::when($mock)->neverReturn()->thenThrow($expectedException = new \RuntimeException());
        try {
            $mock->neverReturn();
        } catch (\Exception $e) {
            $this->assertSame($expectedException, $e);
        }
    }

    public function testCallingNullReturnMockedMethodWillReturnNull(): void
    {
        if (PHP_VERSION_ID < 80200) {
            $this->markTestSkipped('null type is not supported in PHP versions prior to 8.2');
        }
        $mock = Phake::mock(\PhakeTest_NullType::class);

        $this->assertNull($mock->nullReturn());
    }

    public function testCallingFalseReturnMockedMethodWillReturnFalse(): void
    {
        if (PHP_VERSION_ID < 80200) {
            $this->markTestSkipped('false type is not supported in PHP versions prior to 8.2');
        }
        $mock = Phake::mock(\PhakeTest_FalseType::class);

        $this->assertFalse($mock->falseReturn());
    }

    public function testCallingTrueReturnMockedMethodWillReturnTrue(): void
    {
        if (PHP_VERSION_ID < 80200) {
            $this->markTestSkipped('true type is not supported in PHP versions prior to 8.2');
        }
        $mock = Phake::mock(\PhakeTest_TrueType::class);

        $this->assertTrue($mock->trueReturn());
    }

    /**
     * For #239
     */
    public function testChainingDoNothing(): void
    {
        $mock = Phake::mock(PhakeTest_MockedClass::class);

        Phake::when($mock)->foo->thenReturn(42)->thenDoNothing();

        $this->assertEquals(42, $mock->foo());
        $this->assertNull($mock->foo());
    }
}
