<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Body;

use SmartAssert\Compiler\CompilableSource\Model\DeferredResolvableCollectionTrait;
use SmartAssert\Compiler\CompilableSource\Model\Expression\AssignmentExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ClosureExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;
use SmartAssert\Compiler\CompilableSource\Model\HasMetadataInterface;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\Metadata;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;
use SmartAssert\Compiler\CompilableSource\Model\Statement\Statement;
use webignition\Stubble\CollectionItemContext;
use webignition\StubbleResolvable\ResolvableCollection;
use webignition\StubbleResolvable\ResolvableCollectionInterface;
use webignition\StubbleResolvable\ResolvableInterface;
use webignition\StubbleResolvable\ResolvedTemplateMutatorResolvable;

class Body implements BodyInterface, ResolvableCollectionInterface
{
    use DeferredResolvableCollectionTrait;

    /**
     * @var BodyContentInterface[]
     */
    private array $content;

    private MetadataInterface $metadata;

    /**
     * @param BodyContentInterface[] $content
     */
    public function __construct(array $content)
    {
        $this->content = $this->filterContent($content);
        $this->metadata = $this->buildMetadata();
    }

    public static function createEnclosingBody(BodyInterface $body): self
    {
        return new Body([
            new Statement(
                new ClosureExpression($body)
            ),
        ]);
    }

    /**
     * @param array<mixed> $expressions
     *
     * @throws \InvalidArgumentException
     */
    public static function createFromExpressions(array $expressions): self
    {
        $statements = [];

        foreach ($expressions as $index => $expression) {
            if ($expression instanceof ExpressionInterface) {
                $statements[] = new Statement($expression);
            } else {
                throw new \InvalidArgumentException('Non-expression at index ' . (string) $index);
            }
        }

        return new Body($statements);
    }

    public static function createForSingleAssignmentStatement(
        ExpressionInterface $variable,
        ExpressionInterface $value
    ): self {
        return new Body([
            new Statement(
                new AssignmentExpression($variable, $value)
            )
        ]);
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }

    protected function createResolvable(): ResolvableInterface
    {
        $resolvables = [];

        foreach ($this->content as $item) {
            $resolvables[] = new ResolvedTemplateMutatorResolvable(
                $item,
                function (string $resolvedTemplate, ?CollectionItemContext $context): string {
                    return $this->resolvedItemTemplateMutator($resolvedTemplate, $context);
                }
            );
        }

        return ResolvableCollection::create($resolvables);
    }

    private function resolvedItemTemplateMutator(string $resolvedTemplate, ?CollectionItemContext $context): string
    {
        $appendNewLine = $context instanceof CollectionItemContext && false === $context->isLast();
        if ($appendNewLine) {
            $resolvedTemplate .= "\n";
        }

        return $resolvedTemplate;
    }

    /**
     * @param BodyContentInterface[] $content
     *
     * @return BodyContentInterface[]
     */
    private function filterContent(array $content): array
    {
        $filteredContent = [];

        foreach ($content as $item) {
            if ($this->includeContent($item)) {
                $filteredContent[] = clone $item;
            }
        }

        return $filteredContent;
    }

    /**
     * @param mixed $item
     */
    private function includeContent($item): bool
    {
        if (!$item instanceof BodyContentInterface) {
            return false;
        }

        if ($item instanceof self && 0 === count($item->content)) {
            return false;
        }

        return true;
    }

    private function buildMetadata(): MetadataInterface
    {
        $metadata = new Metadata();

        foreach ($this->content as $item) {
            if ($item instanceof HasMetadataInterface) {
                $metadata = $metadata->merge($item->getMetadata());
            }
        }

        return $metadata;
    }
}
