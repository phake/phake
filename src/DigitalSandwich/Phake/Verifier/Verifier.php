<?php

namespace DigitalSandwich\Phake\Verifier;

use DigitalSandwich\Phake\Matcher as Matcher;

require_once('DigitalSandwich/Phake/Matcher/MethodMatcher.php');
require_once('DigitalSandwich/Phake/Verifier/VerifierNotMatchedException.php');

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Verifier
{
	/**
	 * @var IVerifiable 
	 */
	private $verifiableObject;

	/**
	 * @var Matcher\IInvocationMatcher
	 */
	private $invocationMatcher;

	//todo: toss null
	public function __construct(IVerifiable $verifiableObject, Matcher\IInvocationMatcher $invocationMatcher)
	{
		$this->verifiableObject = $verifiableObject;
		$this->invocationMatcher = $invocationMatcher;
	}

	public function __call($method, array $arguments)
	{
		$invocationCount = $this->verifiableObject->__PHAKE__verifyMethodCall(new Matcher\MethodMatcher($method, $arguments));

		if ($this->invocationMatcher->matches($invocationCount))
		{
			return TRUE;
		}
		else
		{
			throw new VerifierNotMatchedException();
		}
	}

	public function getInvocationMatcher()
	{
		return $this->invocationMatcher;
	}
}

?>
