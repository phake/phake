<?php
namespace DigitalSandwich\Phake\Verifier;

use DigitalSandwich\Phake\Phake;
use DigitalSandwich\Phake\Matcher\IInvocationMatcher;

require_once('DigitalSandwich/Phake/Verifier/Verifier.php');
require_once('DigitalSandwich/Phake/Verifier/IVerifiable.php');
require_once('DigitalSandwich/Phake/Phake.php');
require_once('DigitalSandwich/Phake/Matcher/IInvocationMatcher.php');

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class TestClass
{
	public function foo() {
		return 'bar';
	}
}

class VerifierTest extends \PHPUnit_Framework_TestCase
{
	private $mock;

	/**
	 * @var Verifier
	 */
	private $verifier;

	/**
	 * @var IInvocationMatcher
	 */
	private $invocationMatcher;

	public function setUp()
	{
		$this->mock = Phake::mock('\DigitalSandwich\Phake\Verifier\IVerifiable');
		$this->invocationMatcher = Phake::mock('\DigitalSandwich\Phake\Matcher\IInvocationMatcher');
		$this->verifier = new Verifier($this->mock, $this->invocationMatcher);
	}

	public function testVerifierAllowsTests()
	{
		$this->verifierMatchesXTimes(1);
		$this->verifier->randomFunctionTest();
	}

	public function testVerifierCallsVerifiable()
	{
		Phake::when($this->mock)->__PHAKE__verifyMethodCall(
				$this->logicalAnd(
					$this->isInstanceOf('DigitalSandwich\Phake\Matcher\MethodMatcher'),
					$this->attribute($this->equalTo('foo'), 'method'),
					$this->attribute($this->equalTo(array('bar', 123)), 'arguments')
				)
			)->thenReturn(1);

		Phake::when($this->invocationMatcher)->matches(Phake::ANY_PARAMETERS)->thenReturn(1);

		$this->verifier->foo('bar', 123);

		Phake::verify($this->mock)->__PHAKE__verifyMethodCall(
			$this->logicalAnd(
				$this->isInstanceOf('DigitalSandwich\Phake\Matcher\MethodMatcher'),
				$this->attribute($this->equalTo('foo'), 'method'),
				$this->attribute($this->equalTo(array('bar', 123)), 'arguments')
			)
		);
	}

	public function testVerifierReturnsTrue()
	{
		$this->verifierMatchesXTimes(1);

		$this->assertTrue($this->verifier->foo());
	}

	public function testInvocationMatcherCalledOnVerification()
	{
		Phake::when($this->invocationMatcher)->matches(1)->thenReturn(TRUE);
		$this->verifierMatchesXTimes(1);

		$this->verifier->foo();

		Phake::verify($this->invocationMatcher)->matches(1);
	}

	public function testGetInvocationMatcher()
	{
		$this->assertSame($this->invocationMatcher, $this->verifier->getInvocationMatcher());
	}

	/**
	 * @expectedException DigitalSandwich\Phake\Verifier\VerifierNotMatchedException
	 */
	public function testVerifierThrowsOnNoMatches()
	{
		$this->verifierMatchesXTimes(0);

		$this->verifier->foo();
	}

	protected function verifierMatchesXTimes($count)
	{
		Phake::when($this->mock)->__PHAKE__verifyMethodCall(Phake::ANY_PARAMETERS)->thenReturn($count);
		Phake::when($this->invocationMatcher)->matches(PHAKE::ANY_PARAMETERS)->thenReturn($count);
	}
}

?>
