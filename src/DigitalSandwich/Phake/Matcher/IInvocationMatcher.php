<?php
namespace DigitalSandwich\Phake\Matcher;

interface IInvocationMatcher
{
	public function matches($invocationCount);
}

?>
