<?php
namespace DigitalSandwich\Phake\CallInterceptor\Answer;

require_once('DigitalSandwich/Phake/CallInterceptor/Answer/IAnswer.php');

class ExceptionAnswer implements IAnswer
{
	private $exception;

	public function __construct(\Exception $exception)
	{
		$this->exception = $exception;
	}
	
	public function getAnswer()
	{
		throw $this->exception;
	}
}

?>
