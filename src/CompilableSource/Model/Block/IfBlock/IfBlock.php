<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Block\IfBlock;

use SmartAssert\Compiler\CompilableSource\Model\Block\AbstractBlock;
use SmartAssert\Compiler\CompilableSource\Model\Body\BodyInterface;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ExpressionInterface;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;

class IfBlock extends AbstractBlock implements BodyInterface
{
    private const RENDER_TEMPLATE = <<<'EOD'
if ({{ expression }}) {
{{ body }}
}
EOD;

    private ExpressionInterface $expression;

    public function __construct(ExpressionInterface $expression, BodyInterface $body)
    {
        parent::__construct($body);

        $this->expression = $expression;
    }

    public function getMetadata(): MetadataInterface
    {
        $metadata = $this->expression->getMetadata();

        return $metadata->merge(parent::getMetadata());
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'expression' => $this->expression,
            'body' => $this->createResolvableBody(),
        ];
    }
}
