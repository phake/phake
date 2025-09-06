<?php

declare(strict_types=1);

namespace Phake\Proxies;

class SetPropertyBinderProxy implements SetPropertyAnswerProxyInterface
{
    public function __construct(
        private \Phake\Stubber\IAnswerBinder $binder
    ) {
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
