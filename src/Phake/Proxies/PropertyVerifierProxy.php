<?php

declare(strict_types=1);

namespace Phake\Proxies;

class PropertyVerifierProxy
{
    public function __construct(
        private \Phake\CallRecorder\Verifier $verifier,
        private \Phake\Matchers\Factory $matcherFactory,
        private \Phake\CallRecorder\IVerifierMode $mode,
        private \Phake\Client\IClient $client,
        private string $name,
    ) {
    }

    public function set(mixed $value): array
    {
        $arguments = [ $value ];
        $expectation = new \Phake\CallRecorder\CallExpectation(
            $this->verifier->getObject(),
            $this->name . '::set',
            $this->matcherFactory->createMatcherChain($arguments),
            $this->mode
        );

        $result = $this->verifier->verifyCall($expectation);

        return $this->client->processVerifierResult($result);
    }

    public function get(): array
    {
        $expectation = new \Phake\CallRecorder\CallExpectation(
            $this->verifier->getObject(),
            $this->name . '::get',
            null,
            $this->mode
        );

        $result = $this->verifier->verifyCall($expectation);

        return $this->client->processVerifierResult($result);
    }
}
