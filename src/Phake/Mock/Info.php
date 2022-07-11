<?php

declare(strict_types=1);

namespace Phake\Mock;

/*
 * Phake - Mocking Framework
 *
 * Copyright (c) 2010-2022, Mike Lively <mike.lively@sellingsource.com>
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
 * Stores data about the mock object.
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class Info
{
    /**
     * @var string
     */
    private $uniqId;

    /**
     * @var \Phake\CallRecorder\Recorder
     */
    private $recorder;

    /**
     * @var \Phake\Stubber\StubMapper
     */
    private $mapper;

    /**
     * @var \Phake\Stubber\IAnswer
     */
    private $answer;

    /**
     * @var bool
     */
    private $frozen;

    /**
     * @var class-string
     */
    private $name;

    /**
     * @var \Phake\ClassGenerator\InvocationHandler\IInvocationHandler|null
     */
    private $handlerChain;

    /**
     * @param class-string $name
     */
    public function __construct($name, \Phake\CallRecorder\Recorder $recorder, \Phake\Stubber\StubMapper $mapper, \Phake\Stubber\IAnswer $defaultAnswer)
    {
        $this->uniqId = bin2hex(random_bytes(7));
        $this->recorder = $recorder;
        $this->mapper = $mapper;
        $this->answer = $defaultAnswer;
        $this->frozen = false;
        $this->name = $name;
    }

    /**
     * @return \Phake\CallRecorder\Recorder
     */
    public function getCallRecorder()
    {
        return $this->recorder;
    }

    /**
     * @return \Phake\Stubber\StubMapper
     */
    public function getStubMapper()
    {
        return $this->mapper;
    }

    /**
     * @return \Phake\Stubber\IAnswer
     */
    public function getDefaultAnswer()
    {
        return $this->answer;
    }

    /**
     * @return bool
     */
    public function isObjectFrozen()
    {
        return $this->frozen;
    }

    /**
     * @return void
     */
    public function freezeObject()
    {
        $this->frozen = true;
    }

    /**
     * @return void
     */
    public function thawObject()
    {
        $this->frozen = false;
    }

    /**
     * @return \Phake\ClassGenerator\InvocationHandler\IInvocationHandler|null
     */
    public function getHandlerChain()
    {
        return $this->handlerChain;
    }

    /**
     * @return void
     */
    public function setHandlerChain(\Phake\ClassGenerator\InvocationHandler\IInvocationHandler $handlerChain)
    {
        $this->handlerChain = $handlerChain;
    }

    /**
     * @return class-string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return void
     */
    public function resetInfo()
    {
        $this->thawObject();
        $this->mapper->removeAllAnswers();
        $this->recorder->removeAllCalls();
    }
}
