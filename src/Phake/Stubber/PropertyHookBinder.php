<?php
declare(strict_types=1);

namespace Phake\Stubber;

class PropertyHookBinder implements IAnswerBinder
{
    /**
     * Creates a new Answer Binder
     */
    public function __construct(
        private \Phake\Matchers\PropertyHookMatcher $matcher,
        private StubMapper $stubMapper
    ) {
    }

    /**
     * Binds an answer to the the classes
     */
    public function bindAnswer(IAnswer $answer): \Phake\Proxies\AnswerCollectionProxy
    {
        $collection = new AnswerCollection($answer);
        $this->stubMapper->mapPropertyStub($collection, $this->matcher);

        return new \Phake\Proxies\AnswerCollectionProxy($collection);
    }
}
