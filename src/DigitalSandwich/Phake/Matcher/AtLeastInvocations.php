<?php
namespace DigitalSandwich\Phake\Matcher;

require_once('DigitalSandwich/Phake/Matcher/IInvocationMatcher.php');

class AtLeastInvocations implements IInvocationMatcher
{
	private $invocationFloor;

	public function __construct($invocationFloor)
	{
		$this->invocationFloor = $invocationFloor;
	}

	public function matches($invocationCount)
	{
		return $invocationCount >= $this->invocationFloor;
	}
}

?>
