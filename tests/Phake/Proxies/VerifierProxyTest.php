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

namespace Phake\Proxies;

use Phake;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Description of VerifierProxyTest
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class VerifierProxyTest extends TestCase
{
    private Phake\CallRecorder\Verifier $verifier;

    private VerifierProxy $proxy;

    private Phake\Client\IClient $client;

    private array $matchedCalls;

    private Phake\CallRecorder\IVerifierMode $mode;

    public function setUp(): void
    {
        $this->verifier = Phake::mock(Phake\CallRecorder\Verifier::class);
        $this->mode = Phake::mock(Phake\CallRecorder\IVerifierMode::class);
        $this->client = Phake::mock(Phake\Client\IClient::class);
        $this->matchedCalls = [
            Phake::mock(Phake\CallRecorder\CallInfo::class),
            Phake::mock(Phake\CallRecorder\CallInfo::class),
        ];

        $this->proxy = new VerifierProxy($this->verifier, new Phake\Matchers\Factory(), $this->mode, $this->client);
        $obj         = $this->getMockBuilder(Phake\IMock::class)->getMock();
        Phake::when($this->verifier)->getObject()->thenReturn($obj);
        Phake::when($this->mode)->__toString()->thenReturn('exactly 1 times');
        Phake::when($this->client)->processVerifierResult($this->anything())->thenReturn($this->matchedCalls);
    }

    /**
     * Tests that the proxy will call the verifier with the method properly forwarded
     */
    public function testVerifierCallsAreForwardedMethod(): void
    {
        Phake::when($this->verifier)->verifyCall(Phake::anyParameters())->thenReturn(
            new Phake\CallRecorder\VerifierResult(true, [Phake::mock(\Phake\CallRecorder\CallInfo::class)])
        );
        $this->proxy->foo();

        Phake::verify($this->verifier)->verifyCall(Phake::capture($expectation));
        $this->assertEquals('foo', $expectation->getMethod());
    }

    /**
     * Tests that call information from the proxied verifier is returned
     */
    public function testVerifierReturnsCallInfoData(): void
    {
        Phake::when($this->verifier)->verifyCall(Phake::anyParameters())->thenReturn(
            new Phake\CallRecorder\VerifierResult(true, $this->matchedCalls)
        );

        $this->assertSame($this->matchedCalls, $this->proxy->foo());
    }

    /**
     * Tests that verifier calls will forward method arguments properly
     */
    public function testVerifierCallsAreForwardedArguments(): void
    {
        $argumentMatcher = Phake::mock(\Phake\Matchers\IChainableArgumentMatcher::class);

        Phake::when($this->verifier)->verifyCall(Phake::anyParameters())->thenReturn(
            new Phake\CallRecorder\VerifierResult(true, [Phake::mock(\Phake\CallRecorder\CallInfo::class)])
        );
        $this->proxy->foo($argumentMatcher);

        Phake::verify($this->verifier)->verifyCall(Phake::capture($expectation));
        $this->assertEquals($argumentMatcher, $expectation->getArgumentMatcher());
    }

    /**
     * Tests that verifier calls that are not given an argument matcher will generate an equals matcher
     * with the given value.
     */
    public function testProxyTransformsNonMatchersToEqualsMatcher(): void
    {
        $argumentMatcher = new Phake\Matchers\StrictlyEqualsMatcher('test');
        Phake::when($this->verifier)->verifyCall(Phake::anyParameters())->thenReturn(
            new Phake\CallRecorder\VerifierResult(true, [Phake::mock(\Phake\CallRecorder\CallInfo::class)])
        );
        $this->proxy->foo('test');

        Phake::verify($this->verifier)->verifyCall(Phake::capture($expectation));
        $this->assertEquals($argumentMatcher, $expectation->getArgumentMatcher());
    }

    public function testClientResultProcessorIsCalled(): void
    {
        $result = new Phake\CallRecorder\VerifierResult(true, $this->matchedCalls);
        Phake::when($this->verifier)->verifyCall(Phake::anyParameters())->thenReturn($result);

        $this->proxy->foo();

        Phake::verify($this->client)->processVerifierResult($result);
    }

    /**
     * @dataProvider magicGetInvalidData
     */
    #[DataProvider('magicGetInvalidData')]
    public function testMagicGetWithInvalidData(string $invalidData, string $exceptionContains): void
    {
        $this->expectException('InvalidArgumentException');
        $this->proxy->__get($invalidData);
    }

    public static function magicGetInvalidData(): iterable
    {
        return [
            ['1foo', 'cannot start with an integer'],
        ];
    }
}
