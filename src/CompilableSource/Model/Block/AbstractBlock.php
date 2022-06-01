<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Block;

use SmartAssert\Compiler\CompilableSource\Model\Body\BodyInterface;
use SmartAssert\Compiler\CompilableSource\Model\HasMetadataInterface;
use SmartAssert\Compiler\CompilableSource\Model\IndentTrait;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;
use webignition\StubbleResolvable\ResolvableInterface;
use webignition\StubbleResolvable\ResolvedTemplateMutatorResolvable;

abstract class AbstractBlock implements HasMetadataInterface, ResolvableInterface
{
    use IndentTrait;

    protected BodyInterface $body;

    public function __construct(BodyInterface $body)
    {
        $this->body = $body;
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->body->getMetadata();
    }

    protected function createResolvableBody(): ResolvableInterface
    {
        return new ResolvedTemplateMutatorResolvable(
            $this->body,
            function (string $resolvedTemplate): string {
                return rtrim($this->indent($resolvedTemplate));
            }
        );
    }
}
