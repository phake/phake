<?php

declare(strict_types=1);
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
     * @var \Phake\Facade|null
     */
    private static $phake;

    /**
     * @var \Phake\Client\IClient|null
     */
    private static $client;

    /**
     * @var \Phake\ClassGenerator\ILoader|null
     */
    private static $loader;

    /**
     * @var \Phake\ClassGenerator\IInstantiator|null
     */
    private static $instantiator;

    /**
     * @var \Phake\ClassGenerator\MockClass|null
     */
    private static $classGenerator;

    /**
     * @var \Phake\Matchers\Factory|null
     */
    private static $matchersFactory;

    /**
     * Constants identifying supported clients
     */
    public const CLIENT_DEFAULT = 'DEFAULT';
    public const CLIENT_PHPUNIT6 = 'PHPUNIT6';
    public const CLIENT_PHPUNIT7 = 'PHPUNIT7';
    public const CLIENT_PHPUNIT8 = 'PHPUNIT8';
    public const CLIENT_PHPUNIT9 = 'PHPUNIT9';
    public const CLIENT_PHPUNIT10 = 'PHPUNIT10';
    public const CLIENT_PHPUNIT11 = 'PHPUNIT11';
    public const CLIENT_PHPUNIT12 = 'PHPUNIT12';

    /**
     * Returns a new mock object based on the given class name.
     *
     * @phpstan-template T of object
     *
     * @param class-string<T>|array<class-string<T>> $className
     * @param \Phake\Stubber\IAnswerContainer        $defaultAnswer
     *
     * @return \Phake\IMock&T
     */
    public static function mock($className, ?\Phake\Stubber\IAnswerContainer $defaultAnswer = null)
    {
        if (null === $defaultAnswer) {
            $answer = new \Phake\Stubber\Answers\SmartDefaultAnswer();
        } else {
            $answer = $defaultAnswer->getAnswer();
            if (null === $answer) {
                throw new InvalidArgumentException("Provided IAnswerContainer doesn't contain any answer.");
            }
        }

        return self::getPhake()->mock(
            $className,
            self::getMockClassGenerator(),
            new \Phake\CallRecorder\Recorder(),
            $answer
        );
    }

    /**
     * Returns a partial mock that is constructed with the given parameters
     *
     * Calls to this class will be recorded however they will still call the original functionality by default.
     *
     * @phpstan-template T of object
     * @param class-string<T> $className class name
     * @param mixed ...$args the remaining arguments will be passed as constructor arguments
     * @return \Phake\IMock&T
     */
    public static function partialMock($className, ...$args)
    {
        $answer = new \Phake\Stubber\Answers\ParentDelegate();

        return self::getPhake()->mock(
            $className,
            self::getMockClassGenerator(),
            new \Phake\CallRecorder\Recorder(),
            $answer,
            $args
        );
    }

    /**
     * For backwards compatibility
     *
     * @see Phake::partialMock()
     * @phpstan-template T of object
     * @param class-string<T> $className class name
     * @param mixed ...$args The remaining arguments will be passed as constructor arguments
     * @return \Phake\IMock&T
     * @deprecated Please use Phake::partialMock() instead
     */
    public static function partMock($className, ...$args)
    {
        return self::partialMock($className, ...$args);
    }

    /**
     * Create a \Phake\Matchers\Factory that we can re-use multiple times. Creating too many
     * instances of this object is expensive.
     *
     * @return \Phake\Matchers\Factory
     */
    private static function getMatchersFactory()
    {
        if (!self::$matchersFactory) {
            self::$matchersFactory = new \Phake\Matchers\Factory();
        }

        return self::$matchersFactory;
    }

    /**
     * Creates a new verifier for the given mock object.
     *
     * @param \Phake\IMock                      $mock
     * @param \Phake\CallRecorder\IVerifierMode $mode
     *
     * @return \Phake\Proxies\VerifierProxy
     */
    public static function verify(\Phake\IMock $mock, ?\Phake\CallRecorder\IVerifierMode $mode = null)
    {
        if (is_null($mode)) {
            $mode = self::times(1);
        }

        /* @var $info \Phake\Mock\Info */
        $info = Phake::getInfo($mock);
        $verifier = new \Phake\CallRecorder\Verifier($info->getCallRecorder(), $mock);

        return new \Phake\Proxies\VerifierProxy($verifier, self::getMatchersFactory(), $mode, self::getClient());
    }

    /**
     * Creates a new verifier for the given mock object.
     *
     * @param \Phake\IMock                      $mock
     * @param \Phake\CallRecorder\IVerifierMode $mode
     *
     * @return \Phake\Proxies\VerifierProxy
     */
    public static function verifyStatic(\Phake\IMock $mock, ?\Phake\CallRecorder\IVerifierMode $mode = null)
    {
        if (is_null($mode)) {
            $mode = self::times(1);
        }

        /* @var $info \Phake\Mock\Info */
        $info = Phake::getInfo(get_class($mock));
        $verifier = new \Phake\CallRecorder\Verifier($info->getCallRecorder(), get_class($mock));

        return new \Phake\Proxies\VerifierProxy($verifier, self::getMatchersFactory(), $mode, self::getClient());
    }


    /**
     * Creates a new verifier for verifying the magic __call method
     *
     * @param mixed ...$arguments A vararg containing the expected arguments for this call
     *
     * @return \Phake\Proxies\CallVerifierProxy
     */
    public static function verifyCallMethodWith(...$arguments)
    {
        $factory = self::getMatchersFactory();
        return new \Phake\Proxies\CallVerifierProxy($factory->createMatcherChain(
            $arguments
        ), self::getClient(), false);
    }

    /**
     * Creates a new verifier for verifying the magic __call method
     *
     * @param mixed ...$arguments A vararg containing the expected arguments for this call
     *
     * @return \Phake\Proxies\CallVerifierProxy
     */
    public static function verifyStaticCallMethodWith(...$arguments)
    {
        $factory = self::getMatchersFactory();
        return new \Phake\Proxies\CallVerifierProxy($factory->createMatcherChain(
            $arguments
        ), self::getClient(), true);
    }

    /**
     * Allows verification of methods in a particular order
     * @param array ...$calls
     * @return void
     */
    public static function inOrder(...$calls)
    {
        $orderVerifier = new \Phake\CallRecorder\OrderVerifier();

        if (!$orderVerifier->verifyCallsInOrder(self::pullPositionsFromCallInfos($calls))) {
            $result = new \Phake\CallRecorder\VerifierResult(false, [], 'Calls not made in order');
            self::getClient()->processVerifierResult($result);
        }
    }

    /**
     * Allows for verifying that a mock object has no further calls made to it.
     *
     * @param \Phake\IMock ...$mocks
     * @return void
     */
    public static function verifyNoFurtherInteraction(\Phake\IMock ...$mocks)
    {
        $mockFreezer = new \Phake\Mock\Freezer();

        foreach ($mocks as $mock) {
            $mockFreezer->freeze(Phake::getInfo($mock), self::getClient());
            $mockFreezer->freeze(Phake::getInfo(get_class($mock)), self::getClient());
        }
    }

    /**
     * Allows for verifying that no interaction occurred with a mock object
     *
     * @param \Phake\IMock ...$mocks
     * @return void
     */
    public static function verifyNoInteraction(\Phake\IMock ...$mocks)
    {
        foreach ($mocks as $mock) {
            $callRecorder = Phake::getInfo($mock)->getCallRecorder();
            $verifier = new \Phake\CallRecorder\Verifier($callRecorder, $mock);
            self::getClient()->processVerifierResult($verifier->verifyNoCalls());

            $sCallRecorder = Phake::getInfo(get_class($mock))->getCallRecorder();
            $sVerifier = new \Phake\CallRecorder\Verifier($sCallRecorder, get_class($mock));
            self::getClient()->processVerifierResult($sVerifier->verifyNoCalls());
        }
    }

    /**
     * Allows for verifying that no other interaction occurred with a mock object outside of what has already been
     * verified
     *
     * @param \Phake\IMock $mock
     * @return void
     */
    public static function verifyNoOtherInteractions(\Phake\IMock $mock)
    {
        $callRecorder = Phake::getInfo($mock)->getCallRecorder();
        $verifier = new \Phake\CallRecorder\Verifier($callRecorder, $mock);
        self::getClient()->processVerifierResult($verifier->verifyNoOtherCalls());

        $sCallRecorder = Phake::getInfo(get_class($mock))->getCallRecorder();
        $sVerifier = new \Phake\CallRecorder\Verifier($sCallRecorder, get_class($mock));
        self::getClient()->processVerifierResult($sVerifier->verifyNoOtherCalls());
    }

    /**
     * Converts a bunch of call info objects to position objects.
     *
     * @param array $calls
     *
     * @return array
     */
    private static function pullPositionsFromCallInfos(array $calls)
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
     *
     * @param \Phake\IMock $mock
     *
     * @return \Phake\Proxies\StubberProxy
     */
    public static function when(\Phake\IMock $mock)
    {
        return new \Phake\Proxies\StubberProxy($mock, self::getMatchersFactory());
    }

    /**
     * Returns a new static stubber for the given mock object.
     *
     * @param \Phake\IMock $mock
     *
     * @return \Phake\Proxies\StubberProxy
     */
    public static function whenStatic(\Phake\IMock $mock)
    {
        return new \Phake\Proxies\StubberProxy(get_class($mock), self::getMatchersFactory());
    }

    /**
     * Returns a new stubber specifically for the __call() method
     *
     * @param mixed ...$arguments A vararg containing the expected arguments for this call
     *
     * @return \Phake\Proxies\CallStubberProxy
     */
    public static function whenCallMethodWith(...$arguments)
    {
        $factory = self::getMatchersFactory();
        return new \Phake\Proxies\CallStubberProxy($factory->createMatcherChain($arguments), false);
    }

    /**
     * Returns a new stubber specifically for the __call() method
     *
     * @param mixed ...$arguments A vararg containing the expected arguments for this call
     *
     * @return \Phake\Proxies\CallStubberProxy
     */
    public static function whenStaticCallMethodWith(...$arguments)
    {
        $factory = self::getMatchersFactory();
        return new \Phake\Proxies\CallStubberProxy($factory->createMatcherChain($arguments), true);
    }

    /**
     * Resets all calls and stubs on the given mock object
     *
     * @param \Phake\IMock $mock
     * @return void
     */
    public static function reset(\Phake\IMock $mock)
    {
        self::getInfo($mock)->resetInfo();
    }

    /**
     * Resets all calls and stubs on the given mock object and return the original class name
     *
     * @param \Phake\IMock $mock
     * @return string $name
     */
    public static function resetStatic(\Phake\IMock $mock)
    {
        $info = self::getInfo(get_class($mock));
        $info->resetInfo();
        return $info->getName();
    }

    /**
     * Resets all static calls, should be ran on tear downs
     * @return void
     */
    public static function resetStaticInfo()
    {
        self::getPhake()->resetStaticInfo();
    }

    /**
     * Provides methods for creating answers. Used in the api as a fluent way to set default stubs.
     * @return \Phake\Proxies\AnswerBinderProxy
     */
    public static function ifUnstubbed()
    {
        $binder = new \Phake\Stubber\SelfBindingAnswerBinder();
        return new \Phake\Proxies\AnswerBinderProxy($binder);
    }

    /**
     * @param \Phake\Facade $phake
     * @return void
     */
    public static function setPhake(\Phake\Facade $phake)
    {
        self::$phake = $phake;
    }

    /**
     * @psalm-suppress NullableReturnStatement
     * @psalm-suppress InvalidNullableReturnType
     *
     * @return \Phake\Facade
     */
    public static function getPhake()
    {
        if (empty(self::$phake)) {
            self::setPhake(self::createPhake());
        }

        return self::$phake;
    }

    /**
     * @return \Phake\Facade
     */
    public static function createPhake()
    {
        return new \Phake\Facade(new \Phake\Mock\InfoRegistry());
    }

    /**
     * Returns an equals matcher for the given value.
     *
     * @param mixed $value
     *
     * @return \Phake\Matchers\EqualsMatcher
     */
    public static function equalTo($value)
    {
        return new \Phake\Matchers\EqualsMatcher($value, \SebastianBergmann\Comparator\Factory::getInstance());
    }

    /**
     * Returns a capturing matcher that will set the value of a given argument to given variable.
     *
     * @param mixed $value - Will be set to the value of the called argument.
     *
     * @return \Phake\Matchers\ArgumentCaptor
     */
    public static function capture(&$value)
    {
        return new \Phake\Matchers\ArgumentCaptor($value);
    }


    /**
     * Returns a capturing matcher that is bound to store ALL of its calls in the variable passed in.
     *
     * $value will initially be set to an empty array;
     *
     * @param mixed $value - Will be set to the value of the called argument.
     *
     * @return \Phake\Matchers\ArgumentCaptor
     */
    public static function captureAll(&$value)
    {
        $ignore = null;
        $captor = new \Phake\Matchers\ArgumentCaptor($ignore);
        $captor->bindAllCapturedValues($value);
        return $captor;
    }


    /**
     * Returns a setter matcher that will set a reference parameter passed in as an argument to the
     * given value.
     *
     * @param mixed $value - Will be written the reference parameter used by the calling method.
     *
     * @return \Phake\Matchers\ReferenceSetter
     */
    public static function setReference($value)
    {
        return new \Phake\Matchers\ReferenceSetter($value);
    }

    /**
     * Allows verifying an exact number of invocations.
     *
     * @psalm-suppress RedundantCastGivenDocblockType
     *
     * @param int $count
     *
     * @return \Phake\CallRecorder\IVerifierMode
     */
    public static function times($count)
    {
        return new \Phake\CallRecorder\VerifierMode\Times((int) $count);
    }

    /**
     * Allows verifying that there were no invocations. Alias of <code>times(0)</code>.
     * @return \Phake\CallRecorder\IVerifierMode
     */
    public static function never()
    {
        return new \Phake\CallRecorder\VerifierMode\Times(0);
    }

    /**
     * Allows verifying at least <code>$count</code> invocations.
     *
     * @psalm-suppress RedundantCastGivenDocblockType
     *
     * @param int $count
     *
     * @return \Phake\CallRecorder\IVerifierMode
     */
    public static function atLeast($count)
    {
        return new \Phake\CallRecorder\VerifierMode\AtLeast((int) $count);
    }

    /**
     * Allows verifying at most <code>$count</code> invocations.
     *
     * @psalm-suppress RedundantCastGivenDocblockType
     *
     * @param int $count
     *
     * @return \Phake\CallRecorder\IVerifierMode
     */
    public static function atMost($count)
    {
        return new \Phake\CallRecorder\VerifierMode\AtMost((int) $count);
    }

    /**
     * Returns an any parameters matcher to allow matching all invocations of a particular method.
     *
     * @return \Phake\Matchers\AnyParameters
     */
    public static function anyParameters()
    {
        return new \Phake\Matchers\AnyParameters();
    }

    /**
     * Returns an any parameters matcher to allow matching all invocations of a particular method.
     *
     * @return \Phake\Matchers\IgnoreRemainingMatcher
     */
    public static function ignoreRemaining()
    {
        return new \Phake\Matchers\IgnoreRemainingMatcher();
    }

    /**
     * Returns the client currently being used by Phake
     *
     * @return \Phake\Client\IClient
     */
    public static function getClient()
    {
        if (!isset(self::$client)) {
            if (class_exists(\PHPUnit\Framework\TestCase::class)) {
                if (version_compare(\PHPUnit\Runner\Version::id(), '12.0.0') >= 0) {
                    return self::$client = new \Phake\Client\PHPUnit12();
                } elseif (version_compare(\PHPUnit\Runner\Version::id(), '11.0.0') >= 0) {
                    return self::$client = new \Phake\Client\PHPUnit11();
                } elseif (version_compare(\PHPUnit\Runner\Version::id(), '10.0.0') >= 0) {
                    return self::$client = new \Phake\Client\PHPUnit10();
                } elseif (version_compare(\PHPUnit\Runner\Version::id(), '9.0.0') >= 0) {
                    return self::$client = new \Phake\Client\PHPUnit9();
                } elseif (version_compare(\PHPUnit\Runner\Version::id(), '8.0.0') >= 0) {
                    return self::$client = new \Phake\Client\PHPUnit8();
                } elseif (version_compare(\PHPUnit\Runner\Version::id(), '7.0.0') >= 0) {
                    return self::$client = new \Phake\Client\PHPUnit7();
                } elseif (version_compare(\PHPUnit\Runner\Version::id(), '6.0.0') >= 0) {
                    return self::$client = new \Phake\Client\PHPUnit6();
                }
            }
            return self::$client = new \Phake\Client\DefaultClient();
        }
        return self::$client;
    }

    /**
     * Sets the client currently being used by Phake.
     *
     * Accepts either an instance of a \Phake\Client\IClient object OR a string identifying such an object.
     *
     * @param \Phake\Client\IClient|string $client
     * @return void
     */
    public static function setClient($client)
    {
        if ($client instanceof \Phake\Client\IClient) {
            self::$client = $client;
        } elseif (self::CLIENT_PHPUNIT6 == $client) {
            self::$client = new \Phake\Client\PHPUnit6();
        } elseif (self::CLIENT_PHPUNIT7 == $client) {
            self::$client = new \Phake\Client\PHPUnit7();
        } elseif (self::CLIENT_PHPUNIT8 == $client) {
            self::$client = new \Phake\Client\PHPUnit8();
        } elseif (self::CLIENT_PHPUNIT9 == $client) {
            self::$client = new \Phake\Client\PHPUnit9();
        } elseif (self::CLIENT_PHPUNIT10 == $client) {
            self::$client = new \Phake\Client\PHPUnit10();
        } elseif (self::CLIENT_PHPUNIT11 == $client) {
            self::$client = new \Phake\Client\PHPUnit11();
        } elseif (self::CLIENT_PHPUNIT12 == $client) {
            self::$client = new \Phake\Client\PHPUnit12();
        } else {
            self::$client = new \Phake\Client\DefaultClient();
        }
    }

    /**
     * @return \Phake\ClassGenerator\MockClass
     */
    public static function getMockClassGenerator()
    {
        if (!self::$classGenerator) {
            self::$classGenerator = new \Phake\ClassGenerator\MockClass(self::getMockLoader(), self::getMockInstantiator());
        }

        return self::$classGenerator;
    }

    /**
     * @psalm-suppress NullableReturnStatement
     * @psalm-suppress InvalidNullableReturnType
     *
     * @return \Phake\ClassGenerator\ILoader
     */
    public static function getMockLoader()
    {
        if (!self::$loader) {
            self::setMockLoader(new \Phake\ClassGenerator\EvalLoader());
        }

        return self::$loader;
    }

    /**
     * @return void
     */
    public static function setMockLoader(\Phake\ClassGenerator\ILoader $loader)
    {
        self::$classGenerator = null;
        self::$loader = $loader;
    }

    /**
     * @psalm-suppress NullableReturnStatement
     * @psalm-suppress InvalidNullableReturnType
     *
     * @return \Phake\ClassGenerator\IInstantiator
     */
    public static function getMockInstantiator()
    {
        if (!self::$instantiator) {
            self::setMockInstantiator(new \Phake\ClassGenerator\DefaultInstantiator());
        }

        return self::$instantiator;
    }

    /**
     * @return void
     */
    public static function setMockInstantiator(\Phake\ClassGenerator\IInstantiator $instantiator)
    {
        self::$classGenerator = null;
        self::$instantiator = $instantiator;
    }

    /**
     * @param object $obj
     * @return void
     */
    public static function initAnnotations($obj)
    {
        $initializer = new \Phake\Annotation\MockInitializer();
        $initializer->initialize($obj);
    }

    /**
     * Used internally to validate mocks.
     *
     * @internal
     * @param object|string $mock
     * @throws InvalidArgumentException
     * @return void
     */
    public static function assertValidMock($mock)
    {
        if ($mock instanceof \Phake\IMock) {
            return;
        }

        if (is_string($mock) && class_exists($mock, false)) {
            $reflClass = new \ReflectionClass($mock);
            if ($reflClass->implementsInterface(\Phake\IMock::class)) {
                return;
            }
        }

        throw new InvalidArgumentException("Received '" . (is_object($mock) ? get_class($mock) : $mock) . "' Expected an instance of \Phake\IMock or the name of a class that implements \Phake\IMock");
    }

    /**
     * Used internally to standardize pulling mock names.
     *
     * @internal
     * @param \Phake\IMock|class-string $mock
     * @throws InvalidArgumentException
     * @return string
     */
    public static function getName($mock)
    {
        static::assertValidMock($mock);
        return $mock::__PHAKE_name;
    }

    /**
     * Used internally to standardize pulling mock names.
     *
     * @psalm-suppress NoInterfaceProperties
     * @psalm-suppress InvalidPropertyFetch
     *
     * @internal
     * @param \Phake\IMock|class-string $mock
     * @throws InvalidArgumentException
     * @return \Phake\Mock\Info
     */
    public static function getInfo($mock)
    {
        static::assertValidMock($mock);
        if ($mock instanceof \Phake\IMock) {
            assert(isset($mock->__PHAKE_info));
            return $mock->__PHAKE_info;
        }


        assert(isset($mock::$__PHAKE_staticInfo));
        return $mock::$__PHAKE_staticInfo;
    }

    /**
     * Increases allows calling private and protected instance methods on the given mock.
     *
     * @psalm-suppress InternalMethod
     *
     * @param \Phake\IMock $mock
     * @return \Phake\Proxies\VisibilityProxy $mock
     */
    public static function makeVisible(\Phake\IMock $mock)
    {
        return new \Phake\Proxies\VisibilityProxy($mock);
    }

    /**
     * Increases allows calling private and protected static methods on the given mock.
     *
     * @psalm-suppress InternalMethod
     *
     * @param \Phake\IMock $mock
     * @return \Phake\Proxies\StaticVisibilityProxy $mock
     */
    public static function makeStaticsVisible(\Phake\IMock $mock)
    {
        return new \Phake\Proxies\StaticVisibilityProxy($mock);
    }
}
