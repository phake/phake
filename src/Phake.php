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
     * @var Phake_Client_IClient
     */
    private static $client;

    /**
     * @var Phake_ClassGenerator_ILoader
     */
    private static $loader;

    /**
     * Constants identifying supported clients
     */
    const CLIENT_DEFAULT = 'DEFAULT';
    const CLIENT_PHPUNIT = 'PHPUNIT';

    /**
     * Returns a new mock object based on the given class name.
     *
     * @param string                         $className
     * @param Phake_Stubber_IAnswerContainer $defaultAnswer
     *
     * @return mixed
     */
    public static function mock($className, Phake_Stubber_IAnswerContainer $defaultAnswer = null)
    {
        if ($defaultAnswer === null) {
            $answer = new Phake_Stubber_Answers_StaticAnswer(null);
        } else {
            $answer = $defaultAnswer->getAnswer();
        }

        return self::getPhake()->mock(
            $className,
            new Phake_ClassGenerator_MockClass(self::getMockLoader()),
            new Phake_CallRecorder_Recorder(),
            $answer
        );
    }

    /**
     * Returns a partial mock that is constructed with the given parameters
     *
     * Calls to this class will be recorded however they will still call the original functionality by default.
     *
     * @internal param string $className class name
     * @internal param $mixed ... the remaining arguments will be passed as constructor arguments
     * @return Phake_IMock
     */
    public static function partialMock()
    {
        $args      = func_get_args();
        $className = array_shift($args);
        $answer    = new Phake_Stubber_Answers_ParentDelegate();

        return self::getPhake()->mock(
            $className,
            new Phake_ClassGenerator_MockClass(self::getMockLoader()),
            new Phake_CallRecorder_Recorder(),
            $answer,
            $args
        );
    }

    /**
     * For backwards compatibility
     *
     * @see        Phake::partialMock()
     * @internal   param string $className class name
     * @internal   param $mixed ... the remaining arguments will be passed as constructor arguments
     * @return Phake_IMock
     * @deprecated Please use Phake::partialMock() instead
     */
    public static function partMock()
    {
        $args = func_get_args();
        return call_user_func_array('Phake::partialMock', $args);
    }

    /**
     * Creates a new verifier for the given mock object.
     *
     * @param Phake_IMock                      $mock
     * @param Phake_CallRecorder_IVerifierMode $mode
     *
     * @return Phake_Proxies_VerifierProxy
     */
    public static function verify(Phake_IMock $mock, Phake_CallRecorder_IVerifierMode $mode = null)
    {
        if (is_null($mode)) {
            $mode = self::times(1);
        }

        $reader   = new Phake_MockReader();
        $verifier = new Phake_CallRecorder_Verifier($reader->getCallRecorder($mock), $mock);

        return new Phake_Proxies_VerifierProxy($verifier, new Phake_Matchers_Factory(), $mode, self::getClient(
        ), $reader);
    }

    /**
     * Creates a new verifier for verifying the magic __call method
     *
     * @param mixed ... A vararg containing the expected arguments for this call
     *
     * @return Phake_Proxies_CallVerifierProxy
     */
    public static function verifyCallMethodWith()
    {
        $arguments = func_get_args();
        $factory   = new Phake_Matchers_Factory();
        return new Phake_Proxies_CallVerifierProxy($factory->createMatcherArray(
            $arguments
        ), new Phake_MockReader(), self::getClient());
    }

    /**
     * Allows verification of methods in a particular order
     */
    public static function inOrder()
    {
        $calls         = func_get_args();
        $orderVerifier = new Phake_CallRecorder_OrderVerifier();

        if (!$orderVerifier->verifyCallsInOrder(self::pullPositionsFromCallInfos($calls))) {
            $result = new Phake_CallRecorder_VerifierResult(false, array(), "Calls not made in order");
            self::getClient()->processVerifierResult($result);
        }
    }

    /**
     * Allows for verifying that a mock object has no further calls made to it.
     *
     * @param Phake_IMock $mock
     */
    public static function verifyNoFurtherInteraction(Phake_IMock $mock)
    {
        $mockReader  = new Phake_MockReader();
        $mockFreezer = new Phake_Mock_Freezer($mockReader);

        foreach (func_get_args() as $mock) {
            $mockFreezer->freeze($mock, self::getClient());
        }
    }

    /**
     * Allows for verifying that no interaction occurred with a mock object
     *
     * @param Phake_IMock $mock
     */
    public static function verifyNoInteraction(Phake_IMock $mock)
    {
        foreach (func_get_args() as $mock) {
            $reader   = new Phake_MockReader();
            $verifier = new Phake_CallRecorder_Verifier($reader->getCallRecorder($mock), $mock);

            self::getClient()->processVerifierResult($verifier->verifyNoCalls());
        }
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
        $transformed = array();
        foreach ($calls as $callList) {
            $transformedList = array();
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
     * @param Phake_IMock $mock
     *
     * @return Phake_Proxies_StubberProxy
     */
    public static function when(Phake_IMock $mock)
    {
        return new Phake_Proxies_StubberProxy($mock, new Phake_Matchers_Factory(), new Phake_MockReader());
    }

    /**
     * Returns a new stubber specifically for the __call() method
     *
     * @param mixed ... A vararg containing the expected arguments for this call
     *
     * @return \Phake_Proxies_CallStubberProxy
     */
    public static function whenCallMethodWith()
    {
        $arguments = func_get_args();
        $factory   = new Phake_Matchers_Factory();
        return new Phake_Proxies_CallStubberProxy($factory->createMatcherArray($arguments), new Phake_MockReader());
    }

    /**
     * Resets all calls and stubs on the given mock object
     *
     * @param Phake_IMock $mock
     */
    public static function reset(Phake_IMock $mock)
    {
        $mockReader   = new Phake_MockReader();
        $mockResetter = new Phake_Mock_Resetter($mockReader);

        $mockResetter->reset($mock);
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
        if (empty(self::$phake)) {
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
     *
     * @param mixed $value
     *
     * @return Phake_Matchers_EqualsMatcher
     */
    public static function equalTo($value)
    {
        return new Phake_Matchers_EqualsMatcher($value);
    }

    /**
     * Returns a capturing matcher that will set the value of a given argument to given variable.
     *
     * @param mixed $value - Will be set to the value of the called argument.
     *
     * @return Phake_Matchers_ArgumentCaptor
     */
    public static function capture(&$value)
    {
        return new Phake_Matchers_ArgumentCaptor($value);
    }

    /**
     * Returns a setter matcher that will set a reference parameter passed in as an argument to the
     * given value.
     *
     * @param mixed $value - Will be written the reference parameter used by the calling method.
     *
     * @return Phake_Matchers_ReferenceSetter
     */
    public static function setReference($value)
    {
        return new Phake_Matchers_ReferenceSetter($value);
    }

    /**
     * Allows verifying an exact number of invocations.
     *
     * @param int $count
     *
     * @return Phake_CallRecorder_IVerifierMode
     */
    public static function times($count)
    {
        return new Phake_CallRecorder_VerifierMode_Times((int)$count);
    }

    /**
     * Allows verifying that there were no invocations. Alias of <code>times(0)</code>.
     * @return Phake_CallRecorder_IVerifierMode
     */
    public static function never()
    {
        return new Phake_CallRecorder_VerifierMode_Times(0);
    }

    /**
     * Allows verifying at least <code>$count</code> invocations.
     *
     * @param int $count
     *
     * @return Phake_CallRecorder_IVerifierMode
     */
    public static function atLeast($count)
    {
        return new Phake_CallRecorder_VerifierMode_AtLeast((int)$count);
    }

    /**
     * Allows verifying at most <code>$count</code> invocations.
     *
     * @param int $count
     *
     * @return Phake_CallRecorder_IVerifierMode
     */
    public static function atMost($count)
    {
        return new Phake_CallRecorder_VerifierMode_AtMost((int)$count);
    }

    /**
     * Returns an any parameters matcher to allow matching all invocations of a particular method.
     *
     * @return Phake_Matchers_AnyParameters
     */
    public static function anyParameters()
    {
        return new Phake_Matchers_AnyParameters();
    }

    /**
     * Returns the client currently being used by Phake
     *
     * @return Phake_Client_IClient
     */
    public static function getClient()
    {
        if (!isset(self::$client)) {
            return new Phake_Client_Default();
        } else {
            return self::$client;
        }
    }

    /**
     * Sets the client currently being used by Phake.
     *
     * Accepts either an instance of a Phake_Client_IClient object OR a string identifying such an object.
     *
     * @param Phake_Client_IClient|string $client
     */
    public static function setClient($client)
    {
        if ($client instanceof Phake_Client_IClient) {
            self::$client = $client;
        } elseif ($client == self::CLIENT_PHPUNIT) {
            self::$client = new Phake_Client_PHPUnit();
        } else {
            self::$client = new Phake_Client_Default();
        }
    }

    public static function getMockLoader()
    {
        if (isset(self::$loader)) {
            return self::$loader;
        } else {
            return new Phake_ClassGenerator_EvalLoader();
        }
    }

    public static function setMockLoader(Phake_ClassGenerator_ILoader $loader)
    {
        self::$loader = $loader;
    }

    public static function initAnnotations($obj)
    {
        $initializer = new Phake_Annotation_MockInitializer();
        $initializer->initialize($obj);
    }
}
