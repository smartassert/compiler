<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Block\TryCatch;

use SmartAssert\Compiler\CompilableSource\Model\Block\AbstractBlock;

class TryBlock extends AbstractBlock
{
    private const RENDER_TEMPLATE = <<<'EOD'
try {
{{ body }}
}
EOD;

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'body' => $this->createResolvableBody(),
        ];
    }
}
