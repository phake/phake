<?php
namespace DigitalSandwich\Phake\Verifier;

use DigitalSandwich\Phake\Matcher as Matcher;

require_once('DigitalSandwich/Phake/Verifier/IVerifiable.php');

class CallRecorder implements IVerifiable
{
	private $methodInvocations = array();
	private $orderedInvocations = array();

	public function recordCall($method, $args)
	{
		$this->methodInvocations[$method][] = $args;
	}

	public function __PHAKE__verifyMethodCall(Matcher\MethodMatcher $matcher)
	{
		$method = $matcher->getMethod();
		$matchCounter = 0;
		if (isset($this->methodInvocations[$method]))
		{
			foreach ($this->methodInvocations[$method] as $arguments)
			{
				if ($matcher->matches($method, $arguments))
				{
					$matchCounter++;
				}
			}
		}
		return $matchCounter;
	}

	public function getCallRecord()
	{
		$allInvocations = array();
		foreach ($this->methodInvocations as $method => $invocations)
		{
			foreach ($invocations as $arguments)
			{
				$allInvocations[] = array('method' => $method, 'arguments' => $arguments);
			}
		}

		return $allInvocations;
	}
}

?>
