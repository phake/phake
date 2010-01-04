<?php
namespace DigitalSandwich\Phake\Matcher;

require_once('DigitalSandwich/Phake/Matcher/IInvocationMatcher.php');

class AtMostInvocations implements IInvocationMatcher
{
	private $invocationCeiling;

	public function __construct($invocationCeiling)
	{
		$this->invocationCeiling = $invocationCeiling;
	}

	public function matches($invocationCount)
	{
		return $invocationCount <= $this->invocationCeiling;
	}
}

?>
