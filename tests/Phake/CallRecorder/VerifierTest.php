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

require_once('Phake/CallRecorder/Verifier.php');
require_once('Phake/CallRecorder/Call.php');
require_once('Phake/CallRecorder/Recorder.php');

/**
 * Description of VerifierTest
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class Phake_CallRecorder_VerifierTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var Phake_CallRecorder_Recorder
	 */
	private $recorder;

	/**
	 * @var Phake_CallRecorder_Verifier
	 */
	private $verifier;

	/**
	 * Sets up the verifier and its call recorder
	 */
	public function setUp()
	{
		$obj = new stdClass();
		$this->recorder = $this->getMock('Phake_CallRecorder_Recorder');

		$calls = array(
			new Phake_CallRecorder_Call($obj, 'foo'),
			new Phake_CallRecorder_Call($obj, 'bar'),
		);

		$this->recorder->expects($this->any())
			->method('getAllCalls')
			->will($this->returnValue($calls));

		$this->verifier = new Phake_CallRecorder_Verifier($this->recorder, $obj);
	}

	/**
	 * Tests that a verifier can find a call that has been recorded.
	 */
	public function testVerifierFindsCall()
	{
		$this->assertTrue($this->verifier->verifyCall('foo'), 'foo call was not found');
	}

	/**
	 * Tests that a verifier can find a call that has been recorded.
	 */
	public function testVerifierDoesNotFindCall()
	{
		$this->assertFalse($this->verifier->verifyCall('test'), 'test call was found but should not have been');
	}
}
?>
