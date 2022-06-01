<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model;

use webignition\StubbleResolvable\ResolvableInterface;

class ClassSignature implements ResolvableInterface
{
    private const RENDER_TEMPLATE_WITHOUT_BASE_CLASS = 'class {{ name }}';
    private const RENDER_TEMPLATE = self::RENDER_TEMPLATE_WITHOUT_BASE_CLASS . ' extends {{ base_class }}';

    private string $name;
    private ?ClassName $baseClass;

    public function __construct(string $name, ?ClassName $baseClass = null)
    {
        $this->name = $name;
        $this->baseClass = $baseClass;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getBaseClass(): ?ClassName
    {
        return $this->baseClass;
    }

    public function getTemplate(): string
    {
        return $this->baseClass instanceof ClassName
            ? self::RENDER_TEMPLATE
            : self::RENDER_TEMPLATE_WITHOUT_BASE_CLASS;
    }

    public function getContext(): array
    {
        return [
            'name' => $this->getName(),
            'base_class' => $this->baseClass instanceof ClassName ? $this->baseClass->renderClassName() : '',
        ];
    }
}
