<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\TypeDeclaration;

use SmartAssert\Compiler\CompilableSource\Model\DeferredResolvableCollectionTrait;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\Metadata;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;
use webignition\Stubble\CollectionItemContext;
use webignition\StubbleResolvable\ResolvableCollection;
use webignition\StubbleResolvable\ResolvableCollectionInterface;
use webignition\StubbleResolvable\ResolvableInterface;
use webignition\StubbleResolvable\ResolvableWithoutContext;
use webignition\StubbleResolvable\ResolvedTemplateMutationInterface;
use webignition\StubbleResolvable\ResolvedTemplateMutatorResolvable;

class ObjectTypeDeclarationCollection implements
    TypeDeclarationCollectionInterface,
    ResolvedTemplateMutationInterface,
    ResolvableCollectionInterface
{
    use DeferredResolvableCollectionTrait;

    /**
     * @var ObjectTypeDeclaration[]
     */
    private array $declarations;

    /**
     * @param ObjectTypeDeclaration[] $declarations
     */
    public function __construct(array $declarations)
    {
        $this->declarations = array_filter($declarations, function ($item) {
            return $item instanceof ObjectTypeDeclaration;
        });
    }

    public function getMetadata(): MetadataInterface
    {
        $metadata = new Metadata();

        foreach ($this->declarations as $declaration) {
            if ($declaration instanceof TypeDeclarationInterface) {
                $metadata = $metadata->merge($declaration->getMetadata());
            }
        }

        return $metadata;
    }

    /**
     * @return callable[]
     */
    public function getResolvedTemplateMutators(): array
    {
        return [
            function (string $resolvedTemplate): string {
                return $this->resolvedTemplateMutator($resolvedTemplate);
            },
        ];
    }

    protected function createResolvable(): ResolvableInterface
    {
        $resolvableDeclarations = [];
        foreach ($this->declarations as $declaration) {
            $resolvableDeclarations[] = new ResolvedTemplateMutatorResolvable(
                new ResolvableWithoutContext((string) $declaration),
                function (string $resolvedTemplate, ?CollectionItemContext $context) {
                    return $this->declarationResolvedTemplateMutator($resolvedTemplate, $context);
                }
            );
        }

        return ResolvableCollection::create($resolvableDeclarations);
    }

    private function resolvedTemplateMutator(string $resolvedTemplate): string
    {
        $parts = explode(' | ', $resolvedTemplate);
        $parts = array_filter($parts);

        $namespaceSeparator = '\\';
        usort($parts, function (string $a, string $b) use ($namespaceSeparator) {
            $a = ltrim($a, $namespaceSeparator);
            $b = ltrim($b, $namespaceSeparator);

            if ($a === $b) {
                return 0;
            }

            return $a < $b ? -1 : 1;
        });

        return implode(' | ', $parts);
    }

    private function declarationResolvedTemplateMutator(
        string $resolvedTemplate,
        ?CollectionItemContext $context
    ): string {
        $appendLeadingSpace = $context instanceof CollectionItemContext && false === $context->isFirst();
        if ($appendLeadingSpace) {
            $resolvedTemplate = ' ' . $resolvedTemplate;
        }

        $appendSeparator = $context instanceof CollectionItemContext && false === $context->isLast();
        if ($appendSeparator) {
            $resolvedTemplate .= ' |';
        }

        return $resolvedTemplate;
    }
}
