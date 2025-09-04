<?php

declare(strict_types=1);

namespace Phake\Proxies;

class GetPropertyBinderProxy implements GetPropertyAnswerProxyInterface
{
    public function __construct(
        private \Phake\Stubber\IAnswerBinder $binder
    ) {
    }

    public function thenReturn(mixed $value): \Phake\Stubber\IAnswerContainer
    {
        return $this->binder->bindAnswer(new \Phake\Stubber\Answers\StaticAnswer($value));
    }

    public function thenReturnCallback(callable $value): \Phake\Stubber\IAnswerContainer
    {
        return $this->binder->bindAnswer(new \Phake\Stubber\Answers\LambdaAnswer($value));
    }

    public function thenThrow(\Throwable $value): \Phake\Stubber\IAnswerContainer
    {
        return $this->binder->bindAnswer(new \Phake\Stubber\Answers\ExceptionAnswer($value));
    }

    public function thenCallParent(): \Phake\Stubber\IAnswerContainer
    {
        return $this->binder->bindAnswer(new \Phake\Stubber\Answers\ParentDelegate());
    }
}
