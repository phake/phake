<?php

namespace DigitalSandwich\Phake\Verifier;

use DigitalSandwich\Phake\Matcher as Matcher;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

interface IVerifiable
{
	public function __PHAKE__verifyMethodCall(Matcher\MethodMatcher $matcher);
}

?>
