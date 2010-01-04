<?php
namespace DigitalSandwich\Phake\Matcher;

require_once('DigitalSandwich/Phake/Matcher/AtMostInvocations.php');

class AtMostInvocationsTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var AtMostInvocations
	 */
	private $matcher;

	public function setUp()
	{
		$this->matcher = new AtMostInvocations(10);
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

	public function testReturnsTrueOnLesserValue()
	{
		$this->assertTrue($this->matcher->matches(9));
	}
}

?>
