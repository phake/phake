<?php
namespace DigitalSandwich\Phake\CallInterceptor;

require_once('DigitalSandwich/Phake/CallInterceptor/Answer/StaticAnswer.php');
require_once('DigitalSandwich/Phake/CallInterceptor/Answer/ExceptionAnswer.php');

class MethodPlan
{
	/**
	 * @var Answer\IAnswer 
	 */
	private $answer;

	public function thenReturn($value)
	{
		$this->answer = new Answer\StaticAnswer($value);
	}

	public function getAnswer()
	{
		return $this->answer->getAnswer();
	}

	public function thenThrow(\Exception $exception)
	{
		$this->answer = new Answer\ExceptionAnswer($exception);
	}
}

?>
