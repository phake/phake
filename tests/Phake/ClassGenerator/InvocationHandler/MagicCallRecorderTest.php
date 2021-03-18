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

use PHPUnit\Framework\TestCase;

class Phake_ClassGenerator_InvocationHandler_MagicCallRecorderTest extends TestCase
{
    /**
     * @var Phake_ClassGenerator_InvocationHandler_MagicCallRecorder
     */
    private $handler;

    /**
     * @var Phake_CallRecorder_Recorder
     */
    private $callRecorder;

    public function setUp(): void
    {
        $this->callRecorder = Phake::mock('Phake_CallRecorder_Recorder');
        $this->handler = new Phake_ClassGenerator_InvocationHandler_MagicCallRecorder($this->callRecorder);
    }

    public function testImplementIInvocationHandler()
    {
        $this->assertInstanceOf('Phake_ClassGenerator_InvocationHandler_IInvocationHandler', $this->handler);
    }

    public function testMagicCallIsRecorded()
    {
        $mock = $this->getMockBuilder('Phake_IMock')
                    ->getMock();

        $ref = array('foo', array());
        $this->handler->invoke($mock, '__call', array('foo', array()), $ref);

        Phake::verify($this->callRecorder)->recordCall(
            new Phake_CallRecorder_Call($mock, 'foo', array())
        );
    }

    public function testStaticMagicCallIsRecorded()
    {
        $mock = $this->getMockBuilder('Phake_IMock')
                    ->getMock();
        $mockClass = get_class($mock);

        $ref = array('foo', array());
        $this->handler->invoke($mockClass, '__callStatic', array('foo', array()), $ref);

        Phake::verify($this->callRecorder)->recordCall(
            new Phake_CallRecorder_Call($mockClass, 'foo', array())
        );
    }

    public function testNonMagicCallDoesNothing()
    {
        $mock = $this->getMockBuilder('Phake_IMock')
                    ->getMock();

        $ref = array();
        $this->handler->invoke($mock, 'foo', array(), $ref);

        Phake::verifyNoInteraction($this->callRecorder);
    }
}
