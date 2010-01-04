<?php
namespace DigitalSandwich\Phake\CallInterceptor;

use DigitalSandwich\Phake\Phake as Phake;
use DigitalSandwich\Phake\Matcher as Matcher;

require_once('DigitalSandwich/Phake/CallInterceptor/MethodPlanContainer.php');
require_once('DigitalSandwich/Phake/CallInterceptor/MethodPlan.php');
require_once('DigitalSandwich/Phake/Matcher/MethodMatcher.php');
require_once('DigitalSandwich/Phake/Phake.php');

class MethodPlanContainerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var MethodPlanContainer 
	 */
	protected $container;

	/**
	 * @var array
	 */
	protected $methodPlans;

	/**
	 * @var array
	 */
	protected $matchers;

	public function setUp()
	{
		$this->container = new MethodPlanContainer();
		$this->methodPlans = array(
			Phake::mock('DigitalSandwich\Phake\CallInterceptor\MethodPlan'),
			Phake::mock('DigitalSandwich\Phake\CallInterceptor\MethodPlan'),
			Phake::mock('DigitalSandwich\Phake\CallInterceptor\MethodPlan'),
		);

		$this->matchers = array(
			Phake::mock('DigitalSandwich\Phake\Matcher\MethodMatcher', array(), array(), '', FALSE),
			Phake::mock('DigitalSandwich\Phake\Matcher\MethodMatcher', array(), array(), '', FALSE),
			Phake::mock('DigitalSandwich\Phake\Matcher\MethodMatcher', array(), array(), '', FALSE),
		);

		$this->container->__PHAKE__addMethodPlan($this->methodPlans[0], $this->matchers[0]);
		$this->container->__PHAKE__addMethodPlan($this->methodPlans[1], $this->matchers[1]);
		$this->container->__PHAKE__addMethodPlan($this->methodPlans[2], $this->matchers[2]);
	}

	public function testMethodPlanContainer()
	{
		$this->assertType(__NAMESPACE__ . '\IMethodPlanContainer', $this->container);
	}

	public function testGetMatchingPlan()
	{
		Phake::when($this->matchers[0])->matches(Phake::ANY_PARAMETERS)->thenReturn(TRUE);

		$this->assertSame($this->methodPlans[0], $this->container->__PHAKE__getMatchingPlan('foo', array()));

	}
}

?>
