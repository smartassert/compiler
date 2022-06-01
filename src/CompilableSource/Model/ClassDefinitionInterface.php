<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model;

use webignition\StubbleResolvable\ResolvableInterface;

interface ClassDefinitionInterface extends HasMetadataInterface, ResolvableInterface
{
    public function getSignature(): ClassSignature;

    public function getBody(): ClassBody;
}
