<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model;

use SmartAssert\Compiler\CompilableSource\Model\DocBlock\DocBlock;
use webignition\StubbleResolvable\ResolvableInterface;

interface MethodDefinitionInterface extends HasMetadataInterface, ResolvableInterface
{
    /**
     * @return string[]
     */
    public function getArguments(): array;

    public function getName(): string;

    public function getReturnType(): ?string;

    public function getVisibility(): string;

    public function isStatic(): bool;

    public function getDocBlock(): ?DocBlock;

    public function withDocBlock(DocBlock $docBlock): MethodDefinitionInterface;
}
