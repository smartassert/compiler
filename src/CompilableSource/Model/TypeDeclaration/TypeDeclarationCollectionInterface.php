<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\TypeDeclaration;

use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;
use webignition\StubbleResolvable\ResolvableInterface;

interface TypeDeclarationCollectionInterface extends ResolvableInterface
{
    public function getMetadata(): MetadataInterface;
}
