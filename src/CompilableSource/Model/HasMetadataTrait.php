<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model;

use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;

trait HasMetadataTrait
{
    private MetadataInterface $metadata;

    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }
}
