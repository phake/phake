<?php

/**
 * Allows providing a static answer to a stubbed method call.
 *
 * @author Mathieu Kooiman <mathieuk@gmail.com>
 */
class Phake_Stubber_Answers_LambdaAnswer implements Phake_Stubber_Answers_IDelegator, Phake_Stubber_IAnswerDelegate
{
    /**
     * @var mixed
     */
    private $answerLambda;

    /**
     * @param mixed $answerLambda
     */
    public function __construct($answerLambda)
    {
        $this->answerLambda = $answerLambda;
    }

    /**
     * @return mixed
     */
    public function getAnswer()
    {
        return $this;
    }

    /**
     * Actually calls the given lambda to produce the answer
     * @return mixed
     */
    public function getActualAnswer()
    {
        $lambda = $this->answerLambda;
        $args   = func_get_args();
        return call_user_func_array($lambda, $args);
    }

    /**
     * Return callback to produce actual answer
     *
     * @param object $calledObject
     * @param string $calledMethod
     * @param array  $calledParameters
     *
     * @return array callback
     */
    public function getCallBack($calledObject, $calledMethod, array $calledParameters)
    {
        return array($this, 'getActualAnswer');
    }

    /**
     * Passes through the given arguments.
     *
     * @param string $calledMethod
     * @param array  $calledParameters
     *
     * @return array
     */
    public function getArguments($calledMethod, array $calledParameters)
    {
        return $calledParameters;
    }

    /**
     * Nothing to process
     *
     * @param mixed $answer
     *
     * @return null
     */
    public function processAnswer($answer)
    {
    }
}


