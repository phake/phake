<?php
namespace DigitalSandwich\Phake\CallInterceptor;

use DigitalSandwich\Phake\Matcher as Matcher;

require_once('DigitalSandwich/Phake/CallInterceptor/IMethodPlanContainer.php');

class MethodPlanContainer implements IMethodPlanContainer
{
	/**
	 * @var array 
	 */
	private $matchers = array();
	
	/**
	 * @var array 
	 */
	private $methodPlans = array();

	public function __PHAKE__addMethodPlan(MethodPlan $plan, Matcher\MethodMatcher $matcher)
	{
		$this->matchers[] = $matcher;
		$this->methodPlans[spl_object_hash($matcher)] = $plan;
	}

	public function __PHAKE__getMatchingPlan($method, $arguments)
	{
		foreach ($this->matchers as $matcher)
		{
			/* @var $matcher Matcher\MethodMatcher */
			if ($matcher->matches($method, $arguments))
			{
				return $this->methodPlans[spl_object_hash($matcher)];
			}
		}

		return NULL;
	}
}
?>
