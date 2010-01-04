<?php
namespace DigitalSandwich\Phake\CallInterceptor;

require_once('DigitalSandwich/Phake/CallInterceptor/MethodPlan.php');

class MethodPlanTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var MethodPlan
	 */
	protected $methodPlan;

	public function setUp()
	{
		$this->methodPlan = new MethodPlan();
	}

	public function testStaticAnswer()
	{
		$this->methodPlan->thenReturn(240);

		$this->assertEquals(240, $this->methodPlan->getAnswer());
	}

	/**
	 * @expectedException Exception
	 */
	public function testExceptionAnswer()
	{
		try
		{
			$this->methodPlan->thenThrow(new \Exception());
		}
		catch (Exception $e)
		{
			$this->fail("Threw too soon!");
		}
		
		$this->methodPlan->getAnswer();
	}
}

?>
