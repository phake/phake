<?php
namespace DigitalSandwich\Phake\CallInterceptor\Answer;

require_once('DigitalSandwich/Phake/CallInterceptor/Answer/StaticAnswer.php');

class StaticAnswerTest extends \PHPUnit_Framework_TestCase
{
	public function testStaticAnswer()
	{
		$answer = new StaticAnswer(42);

		$this->assertEquals(42, $answer->getAnswer());
	}
}

?>
