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

namespace Phake\CallRecorder;

/**
 * A call or set of calls that was expected
 */
class CallExpectation implements \Stringable
{
    /**
     * @var \Phake\IMock|class-string
     */
    private \Phake\IMock|string $object;

    private string $method;

    private ?\Phake\Matchers\IChainableArgumentMatcher $argumentMatcher;

    private IVerifierMode $verifierMode;

    /**
     * @param \Phake\IMock|class-string $object
     */
    public function __construct(
        \Phake\IMock|string $object,
        string $method,
        ?\Phake\Matchers\IChainableArgumentMatcher $argumentMatcher,
        IVerifierMode $verificationMode
    ) {
        $this->object           = $object;
        $this->method           = $method;
        $this->argumentMatcher  = $argumentMatcher;
        $this->verifierMode     = $verificationMode;
    }

    /**
     * @return \Phake\IMock|class-string
     */
    public function getObject(): \Phake\IMock|string
    {
        return $this->object;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getArgumentMatcher(): ?\Phake\Matchers\IChainableArgumentMatcher
    {
        return $this->argumentMatcher;
    }

    public function getVerifierMode(): IVerifierMode
    {
        return $this->verifierMode;
    }

    public function __toString(): string
    {
        $arguments = [];

        $argumentMatcher = $this->argumentMatcher;

        while (!empty($argumentMatcher)) {
            $arguments[] = $argumentMatcher->__toString();
            $argumentMatcher = $argumentMatcher->getNextMatcher();
        }

        $name = \Phake::getName($this->getObject());
        $access = is_string($this->object) ? '::' : '->';

        return "Expected {$name}{$access}{$this->getMethod()}(" . implode(
            ', ',
            $arguments
        ) . ") to be called {$this->getVerifierMode()->__toString()}";
    }
}
