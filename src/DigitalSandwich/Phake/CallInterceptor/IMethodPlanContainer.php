<?php

namespace DigitalSandwich\Phake\CallInterceptor;

use DigitalSandwich\Phake\Matcher as Matcher;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

interface IMethodPlanContainer
{
	public function __PHAKE__addMethodPlan(MethodPlan $plan, Matcher\MethodMatcher $matcher);

	public function __PHAKE__getMatchingPlan($method, $arguments);
}

?>
