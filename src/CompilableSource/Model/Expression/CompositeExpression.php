<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Expression;

use SmartAssert\Compiler\CompilableSource\Model\DeferredResolvableCreationTrait;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\Metadata;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;
use webignition\StubbleResolvable\ResolvableCollection;
use webignition\StubbleResolvable\ResolvableInterface;

class CompositeExpression implements ExpressionInterface
{
    use DeferredResolvableCreationTrait;

    /**
     * @var ExpressionInterface[]
     */
    private $expressions;

    /**
     * @param array<mixed> $expressions
     */
    public function __construct(array $expressions)
    {
        $this->expressions = array_filter($expressions, function ($item) {
            return $item instanceof ExpressionInterface;
        });

        $metadata = new Metadata();
        foreach ($this->expressions as $expression) {
            $metadata = $metadata->merge($expression->getMetadata());
        }
    }

    public function getMetadata(): MetadataInterface
    {
        $metadata = new Metadata();
        foreach ($this->expressions as $expression) {
            $metadata = $metadata->merge($expression->getMetadata());
        }

        return $metadata;
    }

    protected function createResolvable(): ResolvableInterface
    {
        if (null === $this->resolvable) {
            $this->resolvable = ResolvableCollection::create($this->expressions);
        }

        return $this->resolvable;
    }
}
