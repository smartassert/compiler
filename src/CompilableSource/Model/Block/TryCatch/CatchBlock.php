<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Block\TryCatch;

use SmartAssert\Compiler\CompilableSource\Model\Block\AbstractBlock;
use SmartAssert\Compiler\CompilableSource\Model\Body\BodyInterface;
use SmartAssert\Compiler\CompilableSource\Model\Expression\CatchExpression;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;

class CatchBlock extends AbstractBlock
{
    private const RENDER_TEMPLATE = <<<'EOD'
catch ({{ catch_expression }}) {
{{ body }}
}
EOD;

    private CatchExpression $catchExpression;

    public function __construct(CatchExpression $catchExpression, BodyInterface $body)
    {
        parent::__construct($body);

        $this->catchExpression = $catchExpression;
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'catch_expression' => $this->catchExpression,
            'body' => $this->createResolvableBody(),
        ];
    }

    public function getMetadata(): MetadataInterface
    {
        $metadata = parent::getMetadata();

        return $metadata->merge($this->catchExpression->getMetadata());
    }
}
