<?php

declare(strict_types=1);

namespace Phake\Stubber\Answers;

use PHPUnit\Framework\TestCase;

class LambdaAnswerTest extends TestCase
{
    public function testLambdaAnswerAcceptsOldschoolLambda(): void
    {
        $func   = fn ($arg1) => $arg1;
        $answer = new LambdaAnswer($func);
        $result = $answer->getAnswerCallback('someObject', 'testMethod')('bar');
        $this->assertSame('bar', $result);
    }
}
