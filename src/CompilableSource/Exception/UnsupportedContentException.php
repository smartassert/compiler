<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Exception;

class UnsupportedContentException extends \Exception
{
    public const TYPE_IDENTIFIER = 'identifier';
    public const TYPE_VALUE = 'value';

    public function __construct(
        private string $type,
        private ?string $content
    ) {
        parent::__construct(sprintf('Unsupported content "%s": "%s"', $type, $content));
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }
}
