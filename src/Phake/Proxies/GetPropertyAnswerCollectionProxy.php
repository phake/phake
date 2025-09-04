<?php
declare(strict_types=1);

namespace Phake\Proxies;

class GetPropertyAnswerCollectionProxy implements \Phake\Stubber\IAnswerContainer, GetPropertyAnswerProxyInterface
{
    public function __construct(
        private \Phake\Stubber\AnswerCollection $collection
    ) {
    }

    public function thenReturn(mixed $value): \Phake\Stubber\IAnswerContainer
    {
        $this->collection->addAnswer(new \Phake\Stubber\Answers\StaticAnswer($value));

        return $this;
    }

    public function thenReturnCallback(callable $value): \Phake\Stubber\IAnswerContainer
    {
        $this->collection->addAnswer(new \Phake\Stubber\Answers\LambdaAnswer($value));

        return $this;
    }

    public function thenThrow(\Throwable $value): \Phake\Stubber\IAnswerContainer
    {
        $this->collection->addAnswer(new \Phake\Stubber\Answers\ExceptionAnswer($value));

        return $this;
    }

    public function thenCallParent(): \Phake\Stubber\IAnswerContainer
    {
        $this->collection->addAnswer(new \Phake\Stubber\Answers\ParentDelegate());

        return $this;
    }

    /**
     * Returns an answer from the container
     */
    public function getAnswer(): ?\Phake\Stubber\IAnswer
    {
        return $this->collection->getAnswer();
    }
}
