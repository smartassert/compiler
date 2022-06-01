<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Block;

use SmartAssert\Compiler\CompilableSource\Model\ClassName;
use SmartAssert\Compiler\CompilableSource\Model\DeferredResolvableCollectionTrait;
use SmartAssert\Compiler\CompilableSource\Model\Expression\UseExpression;
use SmartAssert\Compiler\CompilableSource\Model\Statement\Statement;
use webignition\Stubble\CollectionItemContext;
use webignition\StubbleResolvable\ResolvableCollection;
use webignition\StubbleResolvable\ResolvableCollectionInterface;
use webignition\StubbleResolvable\ResolvableInterface;
use webignition\StubbleResolvable\ResolvedTemplateMutationInterface;
use webignition\StubbleResolvable\ResolvedTemplateMutatorResolvable;

class ClassDependencyCollection implements
    \Countable,
    ResolvableInterface,
    ResolvedTemplateMutationInterface,
    ResolvableCollectionInterface
{
    use DeferredResolvableCollectionTrait;

    /**
     * @var ClassName[]
     */
    private array $classNames = [];

    /**
     * @param ClassName[] $classNames
     */
    public function __construct(array $classNames = [])
    {
        foreach ($classNames as $className) {
            if ($className instanceof ClassName && false === $this->containsClassName($className)) {
                $this->classNames[] = $className;
            }
        }
    }

    public function merge(ClassDependencyCollection $collection): ClassDependencyCollection
    {
        return new ClassDependencyCollection(array_merge($this->classNames, $collection->classNames));
    }

    public function count(): int
    {
        return count($this->classNames);
    }

    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    /**
     * @return callable[]
     */
    public function getResolvedTemplateMutators(): array
    {
        return [
            function (string $resolvedTemplate): string {
                $lines = explode("\n", $resolvedTemplate);
                sort($lines);

                return implode("\n", $lines);
            },
        ];
    }

    /**
     * @return ClassName[]
     */
    public function getClassNames(): array
    {
        return $this->classNames;
    }

    protected function createResolvable(): ResolvableInterface
    {
        $useStatementResolvables = [];
        foreach ($this->classNames as $className) {
            $useStatement = new Statement(new UseExpression($className));

            $useStatementResolvables[] = new ResolvedTemplateMutatorResolvable(
                $useStatement,
                function (string $resolvedTemplate, ?CollectionItemContext $context) {
                    return $this->useStatementResolvedTemplateMutator($resolvedTemplate, $context);
                }
            );
        }

        return ResolvableCollection::create($useStatementResolvables);
    }

    private function containsClassName(ClassName $className): bool
    {
        $renderedClassName = (string) $className;

        foreach ($this->classNames as $className) {
            if ((string) $className === $renderedClassName) {
                return true;
            }
        }

        return false;
    }

    private function useStatementResolvedTemplateMutator(
        string $resolvedTemplate,
        ?CollectionItemContext $context
    ): string {
        $appendNewLine = $context instanceof CollectionItemContext && false === $context->isLast();
        if ($appendNewLine) {
            $resolvedTemplate .= "\n";
        }

        return $resolvedTemplate;
    }
}
