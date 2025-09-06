<?php
declare(strict_types=1);

namespace Phake\Proxies;

use Phake\IMock;
use Phake\Stubber\IAnswerContainer;
use Throwable;


class PropertyBinderProxy implements GetPropertyAnswerProxyInterface
{
    public function __construct(
        private string $property,
        private IMock $obj,
        private \Phake\Matchers\Factory $matcherFactory
    ) {
    }

    public function set(mixed $value): SetPropertyBinderProxy  {
        $matcher = new \Phake\Matchers\MethodMatcher($this->property . '::set', $this->matcherFactory->createMatcherChain([$value]));
        $binder  = new \Phake\Stubber\AnswerBinder($matcher, \Phake::getInfo($this->obj)->getStubMapper());

        return new SetPropertyBinderProxy($binder);
    }

    public function get(): GetPropertyAnswerProxyInterface {
        $matcher = new \Phake\Matchers\MethodMatcher($this->property . '::get', null);
        $binder  = new \Phake\Stubber\AnswerBinder($matcher, \Phake::getInfo($this->obj)->getStubMapper());

        return new GetPropertyBinderProxy($binder);
    }

    public function thenReturn(mixed $value): IAnswerContainer
    {
        return $this->get()->thenReturn($value);
    }

    public function thenReturnCallback(callable $value): IAnswerContainer
    {
        return $this->get()->thenReturnCallback($value);
    }

    public function thenCallParent(): IAnswerContainer
    {
        return $this->get()->thenCallParent();
    }

    public function thenThrow(Throwable $value): IAnswerContainer
    {
        return $this->get()->thenThrow($value);
    }
}
