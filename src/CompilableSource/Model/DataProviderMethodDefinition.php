<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model;

use SmartAssert\Compiler\CompilableSource\Model\Body\Body;
use SmartAssert\Compiler\CompilableSource\Model\DocBlock\DocBlock;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ArrayExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ReturnExpression;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\Metadata;
use SmartAssert\Compiler\CompilableSource\Model\Statement\Statement;

class DataProviderMethodDefinition extends MethodDefinition implements DataProviderMethodDefinitionInterface
{
    use HasMetadataTrait;

    /**
     * @var array<mixed>
     */
    private array $data;

    /**
     * @param array<mixed> $data
     */
    public function __construct(string $name, array $data)
    {
        $this->data = $data;

        parent::__construct($name, new Body([
            new Statement(
                new ReturnExpression(
                    ArrayExpression::fromArray($data)
                )
            ),
        ]));

        $this->metadata = new Metadata();
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getArguments(): array
    {
        return [];
    }

    public function getReturnType(): ?string
    {
        return 'array';
    }

    public function getVisibility(): string
    {
        return 'public';
    }

    public function getDocBlock(): ?DocBlock
    {
        return null;
    }

    public function isStatic(): bool
    {
        return false;
    }
}
