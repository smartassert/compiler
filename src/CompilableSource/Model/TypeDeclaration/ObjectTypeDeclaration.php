<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\TypeDeclaration;

use SmartAssert\Compiler\CompilableSource\Model\Block\ClassDependencyCollection;
use SmartAssert\Compiler\CompilableSource\Model\ClassName;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\Metadata;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;
use SmartAssert\Compiler\CompilableSource\Model\ResolvableStringableTrait;

class ObjectTypeDeclaration implements TypeDeclarationInterface
{
    use ResolvableStringableTrait;

    private ClassName $type;
    private MetadataInterface $metadata;

    public function __construct(ClassName $type)
    {
        $this->type = $type;
        $this->metadata = new Metadata([
            Metadata::KEY_CLASS_DEPENDENCIES => new ClassDependencyCollection([
                $this->type,
            ]),
        ]);
    }

    public function __toString(): string
    {
        return $this->type->renderClassName();
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }
}
