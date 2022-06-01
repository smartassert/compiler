<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model;

use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;

interface HasMetadataInterface
{
    public function getMetadata(): MetadataInterface;
}
