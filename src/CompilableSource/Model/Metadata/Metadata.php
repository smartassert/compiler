<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Metadata;

use SmartAssert\Compiler\CompilableSource\Model\Block\ClassDependencyCollection;
use SmartAssert\Compiler\CompilableSource\Model\VariableDependencyCollection;

class Metadata implements MetadataInterface
{
    public const KEY_CLASS_DEPENDENCIES = 'class-dependencies';
    public const KEY_VARIABLE_DEPENDENCIES = 'variable-dependencies';

    private ClassDependencyCollection $classDependencies;
    private VariableDependencyCollection $variableDependencies;

    /**
     * @param array<mixed> $components
     */
    public function __construct(array $components = [])
    {
        $classDependencies = $components[self::KEY_CLASS_DEPENDENCIES] ?? new ClassDependencyCollection();
        $classDependencies = $classDependencies instanceof ClassDependencyCollection
            ? $classDependencies
            : new ClassDependencyCollection();

        $emptyVariableDependencies = new VariableDependencyCollection();
        $variableDependencies = $components[self::KEY_VARIABLE_DEPENDENCIES] ?? $emptyVariableDependencies;
        $variableDependencies = $variableDependencies instanceof VariableDependencyCollection
            ? $variableDependencies
            : $emptyVariableDependencies;

        $this->classDependencies = $classDependencies;
        $this->variableDependencies = $variableDependencies;
    }

    public function getClassDependencies(): ClassDependencyCollection
    {
        return $this->classDependencies;
    }

    public function getVariableDependencies(): VariableDependencyCollection
    {
        return $this->variableDependencies;
    }

    public function merge(MetadataInterface $metadata): MetadataInterface
    {
        $new = new Metadata();
        $new->classDependencies = $this->classDependencies->merge($metadata->getClassDependencies());
        $new->variableDependencies = $this->variableDependencies->merge($metadata->getVariableDependencies());

        return $new;
    }
}
