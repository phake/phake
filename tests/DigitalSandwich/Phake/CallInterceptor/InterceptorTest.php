<?php
namespace DigitalSandwich\Phake\CallInterceptor;

use DigitalSandwich\Phake\Phake as Phake;

require_once('DigitalSandwich/Phake/CallInterceptor/Interceptor.php');
require_once('DigitalSandwich/Phake/CallInterceptor/IMethodPlanContainer.php');
require_once('DigitalSandwich/Phake/CallInterceptor/MethodPlan.php');
require_once('DigitalSandwich/Phake/Phake.php');

class InterceptorTest extends \PHPUnit_Framework_TestCase
{
	private $mock;
	private $interceptor;

	public function setUp()
	{
		$this->mock = Phake::mock('\DigitalSandwich\Phake\CallInterceptor\IMethodPlanContainer');
		$this->interceptor = new Interceptor($this->mock);
	}

	public function testInterceptorReturnsMethodPlan()
	{
		$this->assertType(__NAMESPACE__ . '\MethodPlan', $this->interceptor->foo());
	}

	public function testInterceptorAttachesMethodPlanToMock()
	{
		$this->interceptor->foo(123, 'bar');

		Phake::verify($this->mock)->__PHAKE__addMethodPlan(
			$this->isInstanceOf(__NAMESPACE__ . '\MethodPlan'),
			$this->logicalAnd(
				$this->isInstanceOf('DigitalSandwich\Phake\Matcher\MethodMatcher'),
				$this->attribute($this->equalTo('foo'), 'method'),
				$this->attribute($this->equalTo(array(123, 'bar')), 'arguments')
			)
		);
	}
}

?>
