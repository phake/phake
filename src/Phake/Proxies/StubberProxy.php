<?php
/*
 * Phake - Mocking Framework
 *
 * Copyright (c) 2010-2025, Mike Lively <m@digitalsandwich.com>
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

/**
 * A proxy to handle stubbing a method on a mock object.
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class StubberProxy
{
    use NamedArgumentsResolver;

    /**
     * @param \Phake\IMock|class-string $obj
     */
    public function __construct(
        private \Phake\IMock|string $obj,
        private \Phake\Matchers\Factory $matcherFactory
    ) {
        \Phake::assertValidMock($obj);
    }

    /**
     * A magic call to instantiate an Answer Binder Proxy.
     */
    public function __call(string $method, array $arguments): AnswerBinderProxy
    {
        $matcher = new \Phake\Matchers\MethodMatcher($method, $this->matcherFactory->createMatcherChain($this->resolveNamedArguments($this->obj, $method, $arguments)));
        $binder  = new \Phake\Stubber\AnswerBinder($matcher, \Phake::getInfo($this->obj)->getStubMapper());

        return new AnswerBinderProxy($binder);
    }

    /**
     * @throws \InvalidArgumentException if `__get` is not defined
     */
    public function __get(string|object $name): AnswerBinderProxy|PropertyBinderProxy
    {
        if (is_string($name) && property_exists($this->obj, $name)) {
            if (PHP_VERSION_ID < 80400) {
                throw new \RuntimeException('Stubbing public properties requires PHP 8.4 or higher');
            }
            return new PropertyBinderProxy($name, $this->obj, $this->matcherFactory);
        }

        if (method_exists($this->obj, '__get')) {
            return $this->__call('__get', [$name]);
        }

        throw new \InvalidArgumentException(sprintf("Property '%s' does not exist and __get is not defined", is_string($name) ? $name : gettype($name)));
    }
}
