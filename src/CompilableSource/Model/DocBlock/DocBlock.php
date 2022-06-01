<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\DocBlock;

use SmartAssert\Compiler\CompilableSource\Model\Annotation\AnnotationInterface;
use SmartAssert\Compiler\CompilableSource\Model\DeferredResolvableCollectionTrait;
use webignition\Stubble\CollectionItemContext;
use webignition\StubbleResolvable\ResolvableCollection;
use webignition\StubbleResolvable\ResolvableCollectionInterface;
use webignition\StubbleResolvable\ResolvableInterface;
use webignition\StubbleResolvable\ResolvableWithoutContext;
use webignition\StubbleResolvable\ResolvedTemplateMutationInterface;
use webignition\StubbleResolvable\ResolvedTemplateMutatorResolvable;

class DocBlock implements ResolvableInterface, ResolvedTemplateMutationInterface, ResolvableCollectionInterface
{
    use DeferredResolvableCollectionTrait;

    private const RENDER_TEMPLATE_EMPTY = <<<'EOD'
/**
 */
EOD;

    private const RENDER_TEMPLATE = <<<'EOD'
/**
%s
 */
EOD;

    /**
     * @var array<int, AnnotationInterface|string>
     */
    private array $lines;

    /**
     * @param array<int, AnnotationInterface|string> $lines
     */
    public function __construct(array $lines)
    {
        $this->lines = $lines;
    }

    public function append(DocBlock $addition): self
    {
        return $this->merge($this, $addition);
    }

    public function prepend(DocBlock $addition): self
    {
        return $this->merge($addition, $this);
    }

    /**
     * @return callable[]
     */
    public function getResolvedTemplateMutators(): array
    {
        return [
            function (string $resolvedTemplate): string {
                if ('' === $resolvedTemplate) {
                    return self::RENDER_TEMPLATE_EMPTY;
                }

                return sprintf(self::RENDER_TEMPLATE, $resolvedTemplate);
            },
        ];
    }

    protected function createResolvable(): ResolvableInterface
    {
        $resolvableItems = [];

        foreach ($this->lines as $line) {
            if (is_string($line)) {
                $resolvableItems[] = new ResolvableWithoutContext($line);
            } else {
                $resolvableItems[] = $line;
            }
        }

        array_walk($resolvableItems, function (&$resolvable) {
            $resolvable = new ResolvedTemplateMutatorResolvable(
                $resolvable,
                function (string $resolvedLine, ?CollectionItemContext $context): string {
                    return $this->resolvedLineTemplateMutator($resolvedLine, $context);
                }
            );
        });

        return ResolvableCollection::create($resolvableItems);
    }

    private function merge(DocBlock $source, DocBlock $addition): self
    {
        return new DocBlock(array_merge($source->lines, $addition->lines));
    }

    private function resolvedLineTemplateMutator(string $resolvedLine, ?CollectionItemContext $context): string
    {
        if ('' === trim($resolvedLine)) {
            $resolvedLine = ' *';
        } else {
            $resolvedLine = ' * ' . $resolvedLine;
        }

        $appendNewLine = $context instanceof CollectionItemContext && false === $context->isLast();
        if ($appendNewLine) {
            $resolvedLine .= "\n";
        }

        return $resolvedLine;
    }
}
