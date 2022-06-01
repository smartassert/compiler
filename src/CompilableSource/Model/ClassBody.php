<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model;

use SmartAssert\Compiler\CompilableSource\Model\Metadata\Metadata;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;
use webignition\Stubble\CollectionItemContext;
use webignition\StubbleResolvable\ResolvableCollection;
use webignition\StubbleResolvable\ResolvableCollectionInterface;
use webignition\StubbleResolvable\ResolvableInterface;
use webignition\StubbleResolvable\ResolvedTemplateMutatorResolvable;

class ClassBody implements ResolvableInterface, ResolvableCollectionInterface
{
    use DeferredResolvableCollectionTrait;

    /**
     * @var MethodDefinitionInterface[]
     */
    private array $methods = [];

    /**
     * @param array<mixed> $methods
     */
    public function __construct(array $methods)
    {
        foreach ($methods as $method) {
            if ($method instanceof MethodDefinitionInterface) {
                $this->methods[$method->getName()] = $method;
            }
        }
    }

    /**
     * @return MethodDefinitionInterface[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getMetadata(): MetadataInterface
    {
        $metadata = new Metadata();

        foreach ($this->methods as $method) {
            if ($method instanceof MethodDefinitionInterface) {
                $metadata = $metadata->merge($method->getMetadata());
            }
        }

        return $metadata;
    }

    protected function createResolvable(): ResolvableInterface
    {
        $resolvables = [];

        foreach ($this->methods as $method) {
            $resolvables[] = new ResolvedTemplateMutatorResolvable(
                $method,
                function (string $resolvedTemplate, ?CollectionItemContext $context): string {
                    return $this->methodResolvedTemplateMutator($resolvedTemplate, $context);
                }
            );
        }

        return ResolvableCollection::create($resolvables);
    }

    private function methodResolvedTemplateMutator(string $resolvedTemplate, ?CollectionItemContext $context): string
    {
        $appendNewLine = $context instanceof CollectionItemContext && false === $context->isLast();
        if ($appendNewLine) {
            $resolvedTemplate .= "\n\n";
        }

        return $resolvedTemplate;
    }
}
