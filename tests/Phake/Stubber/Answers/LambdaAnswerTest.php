<?php

class Phake_Stubber_Answers_LambdaAnswerTest extends PHPUnit_Framework_TestCase
{
    public function testLambdaAnswerAcceptsOldschoolLambda()
    {
        $func   = create_function('$arg1', 'return $arg1;');
        $answer = new Phake_Stubber_Answers_LambdaAnswer($func);

        $callback = $answer->getCallback($this, 'foo', array('bar'));

        $result = call_user_func_array($callback, array('bar'));
        $this->assertSame("bar", $result);
    }
}
