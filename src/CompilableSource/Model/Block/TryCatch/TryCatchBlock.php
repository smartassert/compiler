<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Block\TryCatch;

use SmartAssert\Compiler\CompilableSource\Model\Body\BodyInterface;
use SmartAssert\Compiler\CompilableSource\Model\DeferredResolvableCreationTrait;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\Metadata;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;
use webignition\StubbleResolvable\ResolvableCollection;
use webignition\StubbleResolvable\ResolvableInterface;
use webignition\StubbleResolvable\ResolvedTemplateMutatorResolvable;

class TryCatchBlock implements BodyInterface
{
    use DeferredResolvableCreationTrait;

    private TryBlock $tryBlock;

    /**
     * @var CatchBlock[]
     */
    private array $catchBlocks;

    private MetadataInterface $metadata;

    public function __construct(TryBlock $tryBlock, CatchBlock ...$catchBlocks)
    {
        $this->tryBlock = $tryBlock;
        $this->catchBlocks = $catchBlocks;
        $this->metadata = $this->buildMetadata();
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }

    protected function createResolvable(): ResolvableInterface
    {
        $resolvableItems = [
            $this->tryBlock,
        ];

        foreach ($this->catchBlocks as $catchBlock) {
            $resolvableItems[] = new ResolvedTemplateMutatorResolvable(
                $catchBlock,
                function (string $resolvedTemplate) {
                    return $this->catchBlockResolvedTemplateMutator($resolvedTemplate);
                }
            );
        }

        return ResolvableCollection::create($resolvableItems);
    }

    private function buildMetadata(): MetadataInterface
    {
        $metadata = new Metadata();
        $metadata = $metadata->merge($this->tryBlock->getMetadata());

        foreach ($this->catchBlocks as $catchBlock) {
            $metadata = $metadata->merge($catchBlock->getMetadata());
        }

        return $metadata;
    }

    private function catchBlockResolvedTemplateMutator(string $resolvedTemplate): string
    {
        return ' ' . $resolvedTemplate;
    }
}
