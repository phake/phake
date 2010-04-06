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

require_once 'Phake/Facade.php';
require_once 'Phake/ClassGenerator/MockClass.php';
require_once 'Phake/CallRecorder/Recorder.php';
require_once 'Phake/CallRecorder/ICallRecorderContainer.php';
require_once 'Phake/Stubber/StubMapper.php';

/**
 * Tests the facade class for Phake
 *
 * @author Mike Lively <m@digitalsandwich.com>
 */
class Phake_FacadeTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var Phake_Facade
	 */
	private $facade;

	/**
	 * @var Phake_ClassGenerator_MockClass
	 */
	private $mockGenerator;

	/**
	 * @var Phake_CallRecorder_Recorder
	 */
	private $recorder;

	/**
	 * Sets up the mock generator
	 */
	public function setup()
	{
		$this->mockGenerator = $this->getMock('Phake_ClassGenerator_MockClass');
		$this->recorder = $this->getMock('Phake_CallRecorder_Recorder');
		$this->facade = new Phake_Facade($this->mockGenerator, $this->recorder);
	}

	/**
	 * Tests that the mock generator is called properly
	 */
	public function testMock()
	{
		$mockedClass = 'stdClass';

		$this->setMockGeneratorExpectations($mockedClass);

		$this->facade->mock($mockedClass);
	}

	/**
	 * Tests that the mock generator will fail when given a class that does not exist.
	 * @expectedException InvalidArgumentException
	 */
	public function testMockThrowsOnNonExistantClass()
	{
		$mockedClass = 'NonExistantClass';

		$this->facade->mock($mockedClass);
	}

	/**
	 * Tests that Phake will pass a call recorder to a generated class when instantiating it.
	 */
	public function testMockPassesCallRecorderToInstantiatedClass()
	{
		$mockedClass = 'stdClass';

		$this->setMockInstantiatorExpectations();

		$this->facade->mock($mockedClass);
	}

	public function testVerifierInstantiatesVerifier()
	{
		$mock = $this->getMock('Phake_CallRecorder_ICallRecorderContainer');
		
		$mock->expects($this->once())
			->method('__PHAKE_getCallRecorder')
			->will($this->returnValue($this->recorder));

		$this->assertType('Phake_CallRecorder_Verifier', $this->facade->verify($mock));
	}

	/**
	 * Sets expectations for how the generator should be called
	 *
	 * @param string $mockedClass - The class name that we expect to mock
	 */
	private function setMockGeneratorExpectations($mockedClass)
	{
		$this->mockGenerator->expects($this->once())
			->method('generate')
			->with($this->matchesRegularExpression('#^[A-Za-z0-9_]+$#'), $this->equalTo($mockedClass));
	}

	/**
	 * Sets expectations for how the mock class should be created from the class generator
	 */
	private function setMockInstantiatorExpectations()
	{
		$this->mockGenerator->expects($this->once())
				->method('instantiate')
				->with($this->matchesRegularExpression('#^[A-Za-z0-9_]+$#'), $this->equalTo($this->recorder));
	}
}
?>
