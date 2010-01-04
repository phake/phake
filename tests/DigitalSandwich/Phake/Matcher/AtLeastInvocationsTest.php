<?php
namespace DigitalSandwich\Phake\Matcher;

require_once('DigitalSandwich/Phake/Matcher/AtLeastInvocations.php');

class AtLeastInvocationsTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var AtLeastInvocations
	 */
	private $matcher;

	public function setUp()
	{
		$this->matcher = new AtLeastInvocations(10);
	}

	public function testClassImplementsInvocationMatcherInterface()
	{
		$this->assertType('\DigitalSandwich\Phake\Matcher\IInvocationMatcher', $this->matcher);
	}

	public function testReturnsTrueOnEqualValue()
	{
		$this->assertTrue($this->matcher->matches(10));
	}

	public function testReturnsTrueOnGreaterValue()
	{
		$this->assertTrue($this->matcher->matches(11));
	}

	public function testReturnsFalseOnLesserValue()
	{
		$this->assertFalse($this->matcher->matches(9));
	}
}

?>
