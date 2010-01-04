<?php

namespace DigitalSandwich\Phake\CallInterceptor;

use DigitalSandwich\Phake\Matcher;
use \DigitalSandwich\Phake\Phake;

require_once('DigitalSandwich/Phake/CallInterceptor/MethodPlan.php');
require_once('DigitalSandwich/Phake/Matcher/MethodMatcher.php');

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Interceptor
{
	/**
	 * @var IMethodPlanContainer 
	 */
	protected $target;

	public function __construct(IMethodPlanContainer $target)
	{
		$this->target = $target;
	}

	public function __call($method, array $args)
	{
		$methodPlan = new MethodPlan();
		if ($args === array(Phake::ANY_PARAMETERS))
		{
			$args = Phake::ANY_PARAMETERS;
		}
		$matcher = new Matcher\MethodMatcher($method, $args);
		$this->target->__PHAKE__addMethodPlan($methodPlan, $matcher);
		return $methodPlan;
	}
}

?>
