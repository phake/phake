<?php
namespace DigitalSandwich\Phake\Matcher;

use DigitalSandwich\Phake\Phake as Phake;

require_once('DigitalSandwich/Phake/Phake.php');

class MethodMatcher
{
	private $method;
	private $arguments;

	public function __construct($method, $arguments)
	{
		$this->method = $method;

		if (!is_array($arguments)
			&& $arguments != Phake::ANY_PARAMETERS)
		{
			throw new \InvalidArgumentException("Second parameter passed to MethodMatcher constructor should be an array or the Phake::ANY_PARAMETERS constant.");
		}

		$this->arguments = $arguments;
	}

	public function getMethod()
	{
		return $this->method;
	}

	public function matches($method, array $arguments)
	{
		if ($method != $this->method)
		{
			return FALSE;
		}

		if ($this->arguments == Phake::ANY_PARAMETERS)
		{
			return TRUE;
		}

		$checkArgs = $this->arguments;
		foreach ($arguments as $argument)
		{
			if (empty($checkArgs))
			{
				return FALSE;
			}

			$checkArg = array_shift($checkArgs);

			if (!$this->matchArguments($checkArg, $argument))
			{
				return FALSE;
			}
		}
		
		if (!empty($checkArgs))
		{
			return FALSE;
		}

		return TRUE;
	}

	protected function matchArguments($expected, $actual)
	{
		if (class_exists('\PHPUnit_Framework_Constraint') && $expected instanceof \PHPUnit_Framework_Constraint)
		{
			return $this->matchPhpUnitConstraint($expected, $actual);
		}
		elseif (interface_exists('\Hamcrest_Matcher') && $expected instanceof \Hamcrest_Matcher)
		{
			return $this->matchHamcrestMatcher($expected, $actual);
		}
		else
		{
			return $actual === $expected;
		}
	}

	/**
	 *
	 * @param PHPUnit_Framework_Constraint $expected
	 * @param <type> $actual
	 * @return <type>
	 */
	protected function matchPhpUnitConstraint(\PHPUnit_Framework_Constraint $expected, $actual)
	{
		return $expected->evaluate($actual);
	}

	protected function matchHamcrestMatcher(\Hamcrest_Matcher $expected, $actual)
	{
		return $expected->matches($actual);
	}

}
?>
