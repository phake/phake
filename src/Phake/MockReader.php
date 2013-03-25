<?php

/*
 * Phake - Mocking Framework
 * 
 * Copyright (c) 2010, Mike Lively <mike.lively@sellingsource.com>
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
 * Allows reading data off of the mock object.
 *
 * (More of a standard than an enforcement)
 */
class Phake_MockReader
{
    /**
     * @param Phake_IMock $mock
     *
     * @return Phake_CallRecorder_Recorder
     */
    public function getCallRecorder(Phake_IMock $mock)
    {
        return $mock->__PHAKE_callRecorder;
    }

    /**
     * @param Phake_IMock $mock
     *
     * @return string
     */
    public function getName(Phake_IMock $mock)
    {
        return $mock->__PHAKE_name;
    }

    /**
     * @param Phake_IMock $mock
     *
     * @return Phake_Stubber_StubMapper
     */
    public function getStubMapper(Phake_IMock $mock)
    {
        return $mock->__PHAKE_stubMapper;
    }

    /**
     * @param Phake_IMock $mock
     *
     * @return Phake_Stubber_IAnswer
     */
    public function getDefaultAnswer(Phake_IMock $mock)
    {
        return $mock->__PHAKE_defaultAnswer;
    }

    /**
     * @param Phake_IMock $mock
     *
     * @return boolean
     */
    public function isObjectFrozen(Phake_IMock $mock)
    {
        return $mock->__PHAKE_isFrozen;
    }

    /**
     * @param Phake_IMock $mock
     * @param boolean     $frozen
     *
     * @return null
     */
    public function setIsObjectFrozen(Phake_IMock $mock, $frozen)
    {
        $mock->__PHAKE_isFrozen = $frozen;
    }
}
