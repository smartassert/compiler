<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Resolver;

class UnknownElementException extends \Exception
{
    private ?string $testName = null;
    private ?string $stepName = null;
    private ?string $content = null;

    public function __construct(
        private readonly string $elementName,
        ?string $message = null
    ) {
        parent::__construct($message ?? 'Unknown element "' . $elementName . '"');
    }

    public function getElementName(): string
    {
        return $this->elementName;
    }

    public function getTestName(): ?string
    {
        return $this->testName;
    }

    public function setTestName(string $testName): void
    {
        $this->testName = $testName;
    }

    public function getStepName(): ?string
    {
        return $this->stepName;
    }

    public function setStepName(string $stepName): void
    {
        $this->stepName = $stepName;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
