<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Metadata;

use SmartAssert\Compiler\CompilableSource\Model\Block\ClassDependencyCollection;
use SmartAssert\Compiler\CompilableSource\Model\VariableDependencyCollection;

interface MetadataInterface
{
    public function getClassDependencies(): ClassDependencyCollection;

    public function getVariableDependencies(): VariableDependencyCollection;

    public function merge(MetadataInterface $metadata): MetadataInterface;
}
