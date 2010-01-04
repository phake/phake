<?php
namespace DigitalSandwich\Phake\CallInterceptor\Answer;

require_once('DigitalSandwich/Phake/CallInterceptor/Answer/IAnswer.php');

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class StaticAnswer implements IAnswer
{
	private $answer;

	public function __construct($answer)
	{
		$this->answer = $answer;
	}

	public function getAnswer()
	{
		return $this->answer;
	}
}

?>
