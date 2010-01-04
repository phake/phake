<?php
namespace DigitalSandwich\Phake\Verifier;

use DigitalSandwich\Phake\Phake;

require_once('DigitalSandwich/Phake/Verifier/CallRecorder.php');
require_once('DigitalSandwich/Phake/Matcher/MethodMatcher.php');

class CallRecorderTest extends \PHPUnit_Framework_TestCase
{
	private $recorder;

	public function setUp()
	{
		$this->recorder = new CallRecorder();
		$this->recorder->recordCall('foo', array('bar', 123));
		$this->recorder->recordCall('foo', array('bar', 42));
		$this->recorder->recordCall('test', array());
	}

	public function testCallRecorderRecordsCall()
	{
		$this->assertNull($this->recorder->recordCall('foo', array('bar', 123)));
	}

	public function testCallRecorderImplementsIVerifiable()
	{
		$this->assertType('DigitalSandwich\Phake\Verifier\IVerifiable', $this->recorder);
	}

	public function testCallRecorderReturnsZeroOnNoMatches()
	{
		$matcher = Phake::mock('DigitalSandwich\Phake\Matcher\MethodMatcher', array(), array(), '', FALSE);

		Phake::when($matcher)->matches(Phake::ANY_PARAMETERS)->thenReturn(FALSE);
		Phake::when($matcher)->getMethod()->thenReturn('foo');

		$this->assertEquals(0, $this->recorder->__PHAKE__verifyMethodCall($matcher));
	}

	public function testCallRecorderReturnsValueOnMatches()
	{
		$matcher = Phake::mock('DigitalSandwich\Phake\Matcher\MethodMatcher', array(), array(), '', FALSE);

		Phake::when($matcher)->matches(Phake::ANY_PARAMETERS)->thenReturn(TRUE);
		Phake::when($matcher)->getMethod()->thenReturn('test');

		$this->assertGreaterThan(0, $this->recorder->__PHAKE__verifyMethodCall($matcher));
	}

	public function testCallRecorderReturnsTwoOnfooMatches()
	{
		$matcher = Phake::mock('DigitalSandwich\Phake\Matcher\MethodMatcher', array(), array(), '', FALSE);

		Phake::when($matcher)->matches('foo', $this->anything())->thenReturn(TRUE);
		Phake::when($matcher)->getMethod()->thenReturn('foo');

		$this->assertEquals(2, $this->recorder->__PHAKE__verifyMethodCall($matcher));
	}

	public function testGetCallRecord()
	{
		$expectedCallRecord = array(
			array('method' => 'foo', 'arguments' => array('bar', 123)),
			array('method' => 'foo', 'arguments' => array('bar', 42)),
			array('method' => 'test', 'arguments' => array()),
		);

		$this->assertEquals($expectedCallRecord, $this->recorder->getCallRecord());
	}
}

?>
