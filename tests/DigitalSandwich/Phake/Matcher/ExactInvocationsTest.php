<?php
namespace DigitalSandwich\Phake\Matcher;

require_once('DigitalSandwich/Phake/Matcher/ExactInvocations.php');

class ExactInvocationsTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var ExactInvocations
	 */
	private $matcher;

	public function setUp()
	{
		$this->matcher = new ExactInvocations(10);
	}

	public function testClassImplementsInvocationMatcherInterface()
	{
		$this->assertType('\DigitalSandwich\Phake\Matcher\IInvocationMatcher', $this->matcher);
	}

	public function testReturnsTrueOnEqualValue()
	{
		$this->assertTrue($this->matcher->matches(10));
	}

	public function testReturnsFalseOnGreaterValue()
	{
		$this->assertFalse($this->matcher->matches(11));
	}

	public function testReturnsFalseOnLesserValue()
	{
		$this->assertFalse($this->matcher->matches(9));
	}
}

?>
