<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model;

class ClassName
{
    private const FQCN_PART_DELIMITER = '\\';

    private string $className;
    private ?string $alias;

    /**
     * @var string[]
     */
    private static array $specialClasses = [
        'self',
        'static',
        'parent',
    ];

    public function __construct(string $className, ?string $alias = null)
    {
        $this->className = $className;
        $this->alias = $alias;
    }

    public function __toString(): string
    {
        return $this->alias ?? $this->getClass();
    }

    public static function isFullyQualifiedClassName(string $className): bool
    {
        if (in_array($className, self::$specialClasses)) {
            return false;
        }

        return strtolower($className) !== $className;
    }

    public function getClass(): string
    {
        $classNameParts = explode(self::FQCN_PART_DELIMITER, $this->className);

        return array_pop($classNameParts);
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function isInRootNamespace(): bool
    {
        return $this->getClass() === $this->className;
    }

    public function renderClassName(): string
    {
        if (is_string($this->alias)) {
            return $this->alias;
        }

        $rendered = $this->getClass();
        if ($this->isInRootNamespace()) {
            $rendered = '\\' . $rendered;
        }

        return $rendered;
    }
}
