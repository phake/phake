<?php
namespace DigitalSandwich\Phake\Matcher;

use DigitalSandwich\Phake\Phake;

require_once('DigitalSandwich/Phake/Matcher/MethodMatcher.php');
require_once('hamcrest.php');

class MethodMatcherTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var MethodMatcher 
	 */
	private $matcher;

	public function setUp()
	{
		$this->setUpMatcher('foo', array());
	}

	protected function setUpMatcher($method, $args)
	{
		$this->matcher = new MethodMatcher($method, $args);
	}

	public function testMethodMatcherMatchesNoParams()
	{
		$this->assertTrue($this->match('foo', array()));
	}

	public function testMethodMatcherFailsOnBadMethodName()
	{
		$this->assertFalse($this->match('bar', array()));
	}

	public function testMethodMatcherFailsOnBadArgs()
	{
		$this->assertFalse($this->match('foo', array(1)));
	}

	public function testMethodMatcherFailsOnMismatchedArgs()
	{
		$this->setUpMatcher('foo', array(1));
		$this->assertFalse($this->match('foo', array(2)));
	}

	public function testGetMethodName()
	{
		$this->assertEquals('foo', $this->matcher->getMethod());
	}

	public function testMethodMatcherAllowsPHPUnitConstraints()
	{
		$this->setUpMatcher('foo', array($this->equalTo('bar'), $this->equalTo(123), $this->isInstanceOf('\stdClass')));
		$this->assertTrue($this->match('foo', array('bar', 123, new \stdClass())));
	}

	public function testMethodMatcherAllowsHamcrestConstraints()
	{
		$this->setUpMatcher('foo', array(equalTo('bar'), equalTo(123), anInstanceOf('\stdClass')));
		$this->assertTrue($this->match('foo', array('bar', 123, new \stdClass())));
	}

	public function testMethodMatcherAnyParametersShortcut()
	{
		$this->setUpMatcher('foo', Phake::ANY_PARAMETERS);
		$this->assertTrue($this->match('foo', array()));
		$this->assertTrue($this->match('foo', array(23)));
		$this->assertFalse($this->match('boo', array()));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testMethodMatcherThrowsOnBadMatchingShortcut()
	{
		$this->setUpMatcher('foo', 'silly shortcut');
	}

	protected function match($method, array $args)
	{
		return $this->matcher->matches($method, $args);
	}
}

?>
