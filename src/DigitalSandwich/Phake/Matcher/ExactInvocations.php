<?php
namespace DigitalSandwich\Phake\Matcher;

require_once('DigitalSandwich/Phake/Matcher/IInvocationMatcher.php');

class ExactInvocations implements IInvocationMatcher
{
	private $expectedInvocations;

	public function __construct($expectedInvocations)
	{
		$this->expectedInvocations = $expectedInvocations;
	}

	public function matches($invocationCount)
	{
		return $invocationCount == $this->expectedInvocations;
	}
}

?>
