<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Annotation;

use webignition\StubbleResolvable\Resolvable;
use webignition\StubbleResolvable\ResolvableInterface;

abstract class AbstractAnnotation implements AnnotationInterface
{
    private const RENDER_TEMPLATE = '@{{ name }} {{ arguments }}';

    private string $name;

    /**
     * @var string[]
     */
    private array $arguments;

    /**
     * @param string[] $arguments
     */
    public function __construct(string $name, array $arguments)
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    public function getResolvable(): ResolvableInterface
    {
        return new Resolvable(
            self::RENDER_TEMPLATE,
            [
                'name' => $this->name,
                'arguments' => implode(' ', $this->arguments)
            ]
        );
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'name' => $this->name,
            'arguments' => implode(' ', $this->arguments)
        ];
    }
}
