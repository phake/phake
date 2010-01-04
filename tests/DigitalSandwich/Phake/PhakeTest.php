<?php
namespace DigitalSandwich\Phake;

require_once('DigitalSandwich/Phake/Phake.php');
require_once('DigitalSandwich/Phake/Matcher/IInvocationMatcher.php');

class TestClass
{
	public function foo()
	{
		return true;
	}
}

interface TestInterface
{
	public function foo();
}

class PhakeTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var TestClass
	 */
	private $mockClass;

	/**
	 * @var TestInterface
	 */
	private $mockInterface;

	public function setUp()
	{
		$this->mockClass = Phake::mock(__NAMESPACE__ . '\TestClass');
		$this->mockInterface = Phake::mock(__NAMESPACE__ . '\TestInterface');
	}

	public function testBasicMocking()
	{
		$this->assertType(__NAMESPACE__ . '\TestClass', $this->mockClass);
	}

	public function testMockingCreatesChildClass()
	{
		$this->assertNotEquals(__NAMESPACE__ . '\TestClass', get_class($this->mockClass));
	}

	public function testMockingInterface()
	{
		$this->assertType(__NAMESPACE__ . '\TestInterface', $this->mockInterface);
	}

	public function testCallingMockedClassMethodReturnsNull()
	{
		$this->assertNull($this->mockClass->foo());
	}

	public function testCallingMockedInterfaceMethodReturnsNull()
	{
		$this->assertNull($this->mockInterface->foo());
	}

	public function testWhenReturnsCallInterceptor()
	{
		$this->assertType(
			__NAMESPACE__ . '\CallInterceptor\Interceptor',
			Phake::when($this->mockClass)
		);
	}

	public function testStubbingOfAMethod1()
	{
		Phake::when($this->mockClass)->foo()->thenReturn(42);

		$this->assertEquals(42, $this->mockClass->foo());
	}

	public function testStubbingOfAMethod2()
	{
		Phake::when($this->mockClass)->foo('test')->thenReturn(42);

		$this->assertEquals(NULL, $this->mockClass->foo());
		$this->assertEquals(42, $this->mockClass->foo('test'));
	}

	/**
	 * @expectedException Exception
	 */
	public function testStubbingOfAMethod3()
	{
		Phake::when($this->mockClass)->foo('test')->thenThrow(new \Exception());

		$this->assertEquals(NULL, $this->mockClass->foo());
		$this->mockClass->foo('test');
	}


	public function testVerifyReturnsVerifier()
	{
		$this->assertType(__NAMESPACE__ . '\Verifier\Verifier', Phake::verify($this->mockClass));
	}

	public function testCallsAreRecorded()
	{
		$this->mockClass->foo();

		$this->assertTrue(Phake::verify($this->mockClass)->foo());
	}

	/**
	 * @expectedException DigitalSandwich\Phake\Verifier\VerifierNotMatchedException
	 */
	public function testVerificationThrowsOnNonMatch()
	{
		Phake::verify($this->mockClass)->foo();
	}

	public function testGetCallRecord()
	{
		$this->mockClass->foo();

		$callRecord = Phake::getCallRecord($this->mockClass);

		$this->assertEquals(array(array('method' => 'foo', 'arguments' => array())), $callRecord);
	}

	public function testVerifyDefaultsToAtLeastOneInvocationMatcher()
	{
		$verifier = Phake::verify($this->mockClass);

		$this->assertEquals('DigitalSandwich\Phake\Matcher\AtLeastInvocations', get_class($verifier->getInvocationMatcher()));
	}
}

?>
