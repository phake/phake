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
    private static ?Phake\Facade $phake = null;

    private static ?Phake\Client\IClient $client = null;

    private static ?Phake\ClassGenerator\ILoader $loader = null;

    private static ?Phake\ClassGenerator\IInstantiator $instantiator = null;

    private static ?Phake\ClassGenerator\MockClass $classGenerator = null;

    private static ?Phake\Matchers\Factory $matchersFactory = null;

    /**
     * Constants identifying supported clients
     */
    public const CLIENT_DEFAULT = 'DEFAULT';
    public const CLIENT_PHPUNIT8 = 'PHPUNIT8';
    public const CLIENT_PHPUNIT9 = 'PHPUNIT9';
    public const CLIENT_PHPUNIT10 = 'PHPUNIT10';

    /**
     * Returns a new mock object based on the given class name.
     *
     * @template T of object
     *
     * @param class-string<T>|array<class-string<T>> $className
     * @param Phake\Stubber\IAnswerContainer|null   $defaultAnswer
     *
     * @return Phake\IMock&T
     */
    public static function mock(string|array $className, ?Phake\Stubber\IAnswerContainer $defaultAnswer = null): Phake\IMock
    {
        if (null === $defaultAnswer) {
            $answer = new Phake\Stubber\Answers\SmartDefaultAnswer();
        } else {
            $answer = $defaultAnswer->getAnswer();
            if (null === $answer) {
                throw new InvalidArgumentException("Provided IAnswerContainer doesn't contain any answer.");
            }
        }

        return self::getPhake()->mock(
            $className,
            self::getMockClassGenerator(),
            new Phake\CallRecorder\Recorder(),
            $answer
        );
    }

    /**
     * Returns a partial mock that is constructed with the given parameters
     *
     * Calls to this class will be recorded however they will still call the original functionality by default.
     *
     * @template T of object
     * @param class-string<T>|array<class-string<T>> $className class name
     * @param mixed ...$args the remaining arguments will be passed as constructor arguments
     * @return Phake\IMock&T
     */
    public static function partialMock(string|array $className, mixed ...$args): Phake\IMock
    {
        $answer = new Phake\Stubber\Answers\ParentDelegate();

        return self::getPhake()->mock(
            $className,
            self::getMockClassGenerator(),
            new Phake\CallRecorder\Recorder(),
            $answer,
            $args
        );
    }

    /**
     * Create a Phake\Matchers\Factory that we can re-use multiple times. Creating too many
     * instances of this object is expensive.
     */
    private static function getMatchersFactory(): Phake\Matchers\Factory
    {
        if (!isset(self::$matchersFactory)) {
            self::$matchersFactory = new Phake\Matchers\Factory();
        }

        return self::$matchersFactory;
    }

    /**
     * Creates a new verifier for the given mock object.
     */
    public static function verify(Phake\IMock $mock, ?Phake\CallRecorder\IVerifierMode $mode = null): Phake\Proxies\VerifierProxy
    {
        if (is_null($mode)) {
            $mode = self::times(1);
        }

        $info = Phake::getInfo($mock);
        $verifier = new Phake\CallRecorder\Verifier($info->getCallRecorder(), $mock);

        return new Phake\Proxies\VerifierProxy($verifier, self::getMatchersFactory(), $mode, self::getClient());
    }

    /**
     * Creates a new verifier for the given mock object.
     */
    public static function verifyStatic(Phake\IMock $mock, Phake\CallRecorder\IVerifierMode $mode = null): Phake\Proxies\VerifierProxy
    {
        if (is_null($mode)) {
            $mode = self::times(1);
        }

        $info = Phake::getInfo(get_class($mock));
        $verifier = new Phake\CallRecorder\Verifier($info->getCallRecorder(), get_class($mock));

        return new Phake\Proxies\VerifierProxy($verifier, self::getMatchersFactory(), $mode, self::getClient());
    }

    /**
     * Creates a new verifier for verifying the magic __call method
     */
    public static function verifyCallMethodWith(mixed ...$arguments): Phake\Proxies\CallVerifierProxy
    {
        $factory = self::getMatchersFactory();

        return new Phake\Proxies\CallVerifierProxy($factory->createMatcherChain(
            $arguments
        ), self::getClient(), false);
    }

    /**
     * Creates a new verifier for verifying the magic __call method
     */
    public static function verifyStaticCallMethodWith(mixed ...$arguments): Phake\Proxies\CallVerifierProxy
    {
        $factory = self::getMatchersFactory();

        return new Phake\Proxies\CallVerifierProxy($factory->createMatcherChain(
            $arguments
        ), self::getClient(), true);
    }

    /**
     * Allows verification of methods in a particular order
     */
    public static function inOrder(array ...$calls): void
    {
        $orderVerifier = new Phake\CallRecorder\OrderVerifier();

        if (!$orderVerifier->verifyCallsInOrder(self::pullPositionsFromCallInfos($calls))) {
            $result = new Phake\CallRecorder\VerifierResult(false, [], 'Calls not made in order');
            self::getClient()->processVerifierResult($result);
        }
    }

    /**
     * Allows for verifying that a mock object has no further calls made to it.
     */
    public static function verifyNoFurtherInteraction(Phake\IMock ...$mocks): void
    {
        $mockFreezer = new Phake\Mock\Freezer();

        foreach ($mocks as $mock) {
            $mockFreezer->freeze(Phake::getInfo($mock), self::getClient());
            $mockFreezer->freeze(Phake::getInfo(get_class($mock)), self::getClient());
        }
    }

    /**
     * Allows for verifying that no interaction occurred with a mock object
     */
    public static function verifyNoInteraction(Phake\IMock ...$mocks): void
    {
        foreach ($mocks as $mock) {
            $callRecorder = Phake::getInfo($mock)->getCallRecorder();
            $verifier = new Phake\CallRecorder\Verifier($callRecorder, $mock);
            self::getClient()->processVerifierResult($verifier->verifyNoCalls());

            $sCallRecorder = Phake::getInfo(get_class($mock))->getCallRecorder();
            $sVerifier = new Phake\CallRecorder\Verifier($sCallRecorder, get_class($mock));
            self::getClient()->processVerifierResult($sVerifier->verifyNoCalls());
        }
    }

    /**
     * Allows for verifying that no other interaction occurred with a mock object outside of what has already been
     * verified
     */
    public static function verifyNoOtherInteractions(Phake\IMock ...$mocks): void
    {
        foreach ($mocks as $mock) {
            $callRecorder = Phake::getInfo($mock)->getCallRecorder();
            $verifier = new Phake\CallRecorder\Verifier($callRecorder, $mock);
            self::getClient()->processVerifierResult($verifier->verifyNoOtherCalls());

            $sCallRecorder = Phake::getInfo(get_class($mock))->getCallRecorder();
            $sVerifier = new Phake\CallRecorder\Verifier($sCallRecorder, get_class($mock));
            self::getClient()->processVerifierResult($sVerifier->verifyNoOtherCalls());
        }
    }

    /**
     * Converts a bunch of call info objects to position objects.
     */
    private static function pullPositionsFromCallInfos(array $calls): array
    {
        $transformed = [];
        foreach ($calls as $callList) {
            $transformedList = [];
            foreach ($callList as $call) {
                $transformedList[] = $call->getPosition();
            }
            $transformed[] = $transformedList;
        }

        return $transformed;
    }

    /**
     * Returns a new stubber for the given mock object.
     */
    public static function when(Phake\IMock $mock): Phake\Proxies\StubberProxy
    {
        return new Phake\Proxies\StubberProxy($mock, self::getMatchersFactory());
    }

    /**
     * Returns a new static stubber for the given mock object.
     */
    public static function whenStatic(Phake\IMock $mock): Phake\Proxies\StubberProxy
    {
        return new Phake\Proxies\StubberProxy(get_class($mock), self::getMatchersFactory());
    }

    /**
     * Returns a new stubber specifically for the __call() method
     */
    public static function whenCallMethodWith(mixed ...$arguments): Phake\Proxies\CallStubberProxy
    {
        $factory = self::getMatchersFactory();

        return new Phake\Proxies\CallStubberProxy($factory->createMatcherChain($arguments), false);
    }

    /**
     * Returns a new stubber specifically for the __call() method
     */
    public static function whenStaticCallMethodWith(mixed ...$arguments): Phake\Proxies\CallStubberProxy
    {
        $factory = self::getMatchersFactory();

        return new Phake\Proxies\CallStubberProxy($factory->createMatcherChain($arguments), true);
    }

    /**
     * Resets all calls and stubs on the given mock object
     */
    public static function reset(Phake\IMock $mock): void
    {
        self::getInfo($mock)->resetInfo();
    }

    /**
     * Resets all calls and stubs on the given mock object and return the original class name
     */
    public static function resetStatic(Phake\IMock $mock): string
    {
        $info = self::getInfo(get_class($mock));
        $info->resetInfo();

        return $info->getName();
    }

    /**
     * Resets all static calls, should be ran on tear downs
     */
    public static function resetStaticInfo(): void
    {
        self::getPhake()->resetStaticInfo();
    }

    /**
     * Provides methods for creating answers. Used in the api as a fluent way to set default stubs.
     */
    public static function ifUnstubbed(): Phake\Proxies\AnswerBinderProxy
    {
        $binder = new Phake\Stubber\SelfBindingAnswerBinder();

        return new Phake\Proxies\AnswerBinderProxy($binder);
    }

    public static function setPhake(Phake\Facade $phake): void
    {
        self::$phake = $phake;
    }

    /**
     * @psalm-suppress NullableReturnStatement
     * @psalm-suppress InvalidNullableReturnType
     */
    public static function getPhake(): Phake\Facade
    {
        if (!isset(self::$phake)) {
            self::setPhake(self::createPhake());
        }

        return self::$phake;
    }

    public static function createPhake(): Phake\Facade
    {
        return new Phake\Facade(new Phake\Mock\InfoRegistry());
    }

    /**
     * Returns an equals matcher for the given value.
     */
    public static function equalTo(mixed $value): Phake\Matchers\EqualsMatcher
    {
        return new Phake\Matchers\EqualsMatcher($value, \SebastianBergmann\Comparator\Factory::getInstance());
    }

    /**
     * Returns a capturing matcher that will set the value of a given argument to given variable.
     */
    public static function capture(mixed &$value): Phake\Matchers\ArgumentCaptor
    {
        return new Phake\Matchers\ArgumentCaptor($value);
    }

    /**
     * Returns a capturing matcher that is bound to store ALL of its calls in the variable passed in.
     *
     * $value will initially be set to an empty array;
     */
    public static function captureAll(mixed &$value): Phake\Matchers\ArgumentCaptor
    {
        $ignore = null;
        $captor = new Phake\Matchers\ArgumentCaptor($ignore);
        $captor->bindAllCapturedValues($value);

        return $captor;
    }

    /**
     * Returns a setter matcher that will set a reference parameter passed in as an argument to the
     * given value.
     */
    public static function setReference(mixed $value): Phake\Matchers\ReferenceSetter
    {
        return new Phake\Matchers\ReferenceSetter($value);
    }

    /**
     * Allows verifying an exact number of invocations.
     */
    public static function times(int $count): Phake\CallRecorder\IVerifierMode
    {
        return new Phake\CallRecorder\VerifierMode\Times($count);
    }

    /**
     * Allows verifying that there were no invocations. Alias of <code>times(0)</code>.
     */
    public static function never(): Phake\CallRecorder\IVerifierMode
    {
        return new Phake\CallRecorder\VerifierMode\Times(0);
    }

    /**
     * Allows verifying at least <code>$count</code> invocations.
     */
    public static function atLeast(int $count): Phake\CallRecorder\IVerifierMode
    {
        return new Phake\CallRecorder\VerifierMode\AtLeast($count);
    }

    /**
     * Allows verifying at most <code>$count</code> invocations.
     *
     */
    public static function atMost(int $count): Phake\CallRecorder\IVerifierMode
    {
        return new Phake\CallRecorder\VerifierMode\AtMost($count);
    }

    /**
     * Returns an any parameters matcher to allow matching all invocations of a particular method.
     */
    public static function anyParameters(): Phake\Matchers\AnyParameters
    {
        return new Phake\Matchers\AnyParameters();
    }

    /**
     * Returns an any parameters matcher to allow matching all invocations of a particular method.
     */
    public static function ignoreRemaining(): Phake\Matchers\IgnoreRemainingMatcher
    {
        return new Phake\Matchers\IgnoreRemainingMatcher();
    }

    /**
     * Returns the client currently being used by Phake
     */
    public static function getClient(): Phake\Client\IClient
    {
        if (!isset(self::$client)) {
            if (class_exists(\PHPUnit\Framework\TestCase::class)) {
                if (version_compare(\PHPUnit\Runner\Version::id(), '10.0.0') >= 0) {
                    return self::$client = new Phake\Client\PHPUnit10();
                } elseif (version_compare(\PHPUnit\Runner\Version::id(), '9.0.0') >= 0) {
                    return self::$client = new Phake\Client\PHPUnit9();
                } elseif (version_compare(\PHPUnit\Runner\Version::id(), '8.0.0') >= 0) {
                    return self::$client = new \Phake\Client\PHPUnit8();
                }
            }

            return self::$client = new Phake\Client\DefaultClient();
        }

        return self::$client;
    }

    /**
     * Sets the client currently being used by Phake.
     *
     * Accepts either an instance of a Phake\Client\IClient object OR a string identifying such an object.
     */
    public static function setClient(Phake\Client\IClient|string|null $client): void
    {
        if ($client instanceof Phake\Client\IClient) {
            self::$client = $client;
        } elseif (self::CLIENT_PHPUNIT8 == $client) {
            self::$client = new Phake\Client\PHPUnit8();
        } elseif (self::CLIENT_PHPUNIT9 == $client) {
            self::$client = new Phake\Client\PHPUnit9();
        } elseif (self::CLIENT_PHPUNIT10 == $client) {
            self::$client = new Phake\Client\PHPUnit10();
        } else {
            self::$client = new Phake\Client\DefaultClient();
        }
    }

    public static function getMockClassGenerator(): Phake\ClassGenerator\MockClass
    {
        if (!isset(self::$classGenerator)) {
            self::$classGenerator = new Phake\ClassGenerator\MockClass(self::getMockLoader(), self::getMockInstantiator());
        }

        return self::$classGenerator;
    }

    /**
     * @psalm-suppress NullableReturnStatement
     * @psalm-suppress InvalidNullableReturnType
     */
    public static function getMockLoader(): Phake\ClassGenerator\ILoader
    {
        if (!isset(self::$loader)) {
            self::setMockLoader(new Phake\ClassGenerator\EvalLoader());
        }

        return self::$loader;
    }

    public static function setMockLoader(Phake\ClassGenerator\ILoader $loader): void
    {
        self::$classGenerator = null;
        self::$loader = $loader;
    }

    /**
     * @psalm-suppress NullableReturnStatement
     * @psalm-suppress InvalidNullableReturnType
     */
    public static function getMockInstantiator(): Phake\ClassGenerator\IInstantiator
    {
        if (!isset(self::$instantiator)) {
            self::setMockInstantiator(new Phake\ClassGenerator\DefaultInstantiator());
        }

        return self::$instantiator;
    }

    public static function setMockInstantiator(Phake\ClassGenerator\IInstantiator $instantiator): void
    {
        self::$classGenerator = null;
        self::$instantiator = $instantiator;
    }

    public static function initAnnotations(object $obj): void
    {
        $initializer = new Phake\Annotation\MockInitializer();
        $initializer->initialize($obj);
    }

    /**
     * Used internally to validate mocks.
     *
     * @internal
     * @throws InvalidArgumentException
     */
    public static function assertValidMock(object|string $mock): void
    {
        if ($mock instanceof Phake\IMock) {
            return;
        }

        if (is_string($mock) && class_exists($mock, false)) {
            $reflClass = new \ReflectionClass($mock);
            if ($reflClass->implementsInterface(Phake\IMock::class)) {
                return;
            }
        }

        throw new InvalidArgumentException("Received '" . (is_object($mock) ? get_class($mock) : $mock) . "' Expected an instance of \Phake\IMock or the name of a class that implements \Phake\IMock");
    }

    /**
     * Used internally to standardize pulling mock names.
     *
     * @internal
     * @param Phake\IMock|class-string $mock
     * @throws InvalidArgumentException
     */
    public static function getName(Phake\IMock|string $mock): string
    {
        static::assertValidMock($mock);

        return $mock::__PHAKE_name;
    }

    /**
     * Used internally to standardize pulling mock names.
     *
     * @internal
     * @param Phake\IMock|class-string $mock
     * @throws InvalidArgumentException
     */
    public static function getInfo(Phake\IMock|string $mock): Phake\Mock\Info
    {
        static::assertValidMock($mock);

        return self::getPhake()->getInfo($mock);
    }

    /**
     * Increases allows calling private and protected instance methods on the given mock.
     */
    public static function makeVisible(Phake\IMock $mock): Phake\Proxies\VisibilityProxy
    {
        return new Phake\Proxies\VisibilityProxy($mock);
    }

    /**
     * Increases allows calling private and protected static methods on the given mock.
     */
    public static function makeStaticsVisible(Phake\IMock $mock): Phake\Proxies\StaticVisibilityProxy
    {
        return new Phake\Proxies\StaticVisibilityProxy($mock);
    }
}
