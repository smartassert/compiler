<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model\Expression;

use SmartAssert\Compiler\CompilableSource\Model\Body\BodyInterface;
use SmartAssert\Compiler\CompilableSource\Model\IndentTrait;
use SmartAssert\Compiler\CompilableSource\Model\Metadata\MetadataInterface;
use webignition\StubbleResolvable\ResolvedTemplateMutatorResolvable;

class ClosureExpression implements ExpressionInterface
{
    use IndentTrait;

    private const RENDER_TEMPLATE = <<<'EOD'
(function () {
{{ body }}
})()
EOD;

    private BodyInterface $body;

    public function __construct(BodyInterface $body)
    {
        $this->body = $body;
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->body->getMetadata();
    }

    public function getTemplate(): string
    {
        return self::RENDER_TEMPLATE;
    }

    public function getContext(): array
    {
        return [
            'body' => new ResolvedTemplateMutatorResolvable(
                $this->body,
                function (string $resolvedTemplate): string {
                    return rtrim($this->indent($resolvedTemplate));
                }
            ),
        ];
    }
}
