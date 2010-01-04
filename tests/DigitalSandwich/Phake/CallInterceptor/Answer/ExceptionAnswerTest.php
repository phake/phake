<?php
namespace DigitalSandwich\Phake\CallInterceptor\Answer;

require_once('DigitalSandwich/Phake/CallInterceptor/Answer/ExceptionAnswer.php');

class ExceptionAnswerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException Exception
	 */
	public function testExceptionAnswer()
	{
		$answer = new ExceptionAnswer(new \Exception());
		$answer->getAnswer();
	}
}

?>
