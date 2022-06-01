<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\MethodArguments;

use SmartAssert\Compiler\CompilableSource\Model\DeferredResolvableCreationTrait;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;
use SmartAssert\Compiler\CompilableSource\Model\HasMetadataTrait;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\Metadata;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;
use webignition\StubbleResolvable\ResolvableCollection;
use webignition\StubbleResolvable\ResolvableInterface;
use webignition\StubbleResolvable\ResolvedTemplateMutationInterface;
use webignition\StubbleResolvable\ResolvedTemplateMutatorResolvable;

class MethodArguments implements MethodArgumentsInterface, ResolvedTemplateMutationInterface
{
    use DeferredResolvableCreationTrait;
    use HasMetadataTrait;

    public const FORMAT_INLINE = 'inline';
    public const FORMAT_STACKED = 'stacked';

    private const INDENT = '    ';

    private string $format;

    /**
     * @var ExpressionInterface[]
     */
    private array $arguments;

    /**
     * @param ExpressionInterface[] $arguments
     */
    public function __construct(array $arguments = [], string $format = self::FORMAT_INLINE)
    {
        $arguments = array_filter($arguments, function ($item) {
            return $item instanceof ExpressionInterface;
        });

        $this->metadata = new Metadata();
        foreach ($arguments as $expression) {
            $this->metadata = $this->metadata->merge($expression->getMetadata());
        }

        $this->arguments = $arguments;
        $this->format = $format;
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getFormat(): string
    {
        return $this->format;
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
        $resolvableArguments = [];

        foreach ($this->arguments as $argument) {
            $resolvableArguments[] = new ResolvedTemplateMutatorResolvable(
                $argument,
                function (string $resolvedTemplate) {
                    return $this->argumentResolvedTemplateMutator($resolvedTemplate);
                }
            );
        }

        return ResolvableCollection::create($resolvableArguments);
    }

    private function resolvedTemplateMutator(string $resolvedTemplate): string
    {
        if ('' === $resolvedTemplate) {
            return $resolvedTemplate;
        }

        return self::FORMAT_STACKED === $this->format
            ? $this->stackedResolvedTemplateMutator($resolvedTemplate)
            : rtrim($resolvedTemplate, ', ');
    }

    private function stackedResolvedTemplateMutator(string $resolvedTemplate): string
    {
        $resolvedTemplate = rtrim($resolvedTemplate, ",\n");

        $lines = explode("\n", $resolvedTemplate);
        array_walk($lines, function (string &$line) {
            if ('' !== $line) {
                $line = self::INDENT . $line;
            }
        });

        return "\n" . implode("\n", $lines) . "\n";
    }

    private function argumentResolvedTemplateMutator(string $resolvedTemplate): string
    {
        if ('' === $resolvedTemplate) {
            return $resolvedTemplate;
        }

        return self::FORMAT_STACKED === $this->format
            ? $resolvedTemplate . ',' . "\n"
            : $resolvedTemplate . ', ';
    }
}
