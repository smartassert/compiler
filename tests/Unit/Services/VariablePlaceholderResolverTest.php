<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Services\VariablePlaceholderResolver;
use webignition\Stubble\Resolvable\Resolvable;
use webignition\Stubble\Resolvable\ResolvableInterface;

class VariablePlaceholderResolverTest extends TestCase
{
    private VariablePlaceholderResolver $variablePlaceholderResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->variablePlaceholderResolver = new VariablePlaceholderResolver();
    }

    /**
     * @dataProvider resolveDataProvider
     */
    public function testResolve(ResolvableInterface $resolvable, string $expectedResolvedTemplate): void
    {
        $resolvedContent = $this->variablePlaceholderResolver->resolve($resolvable);

        $this->assertSame($expectedResolvedTemplate, $resolvedContent);
    }

    /**
     * @return array<mixed>
     */
    public static function resolveDataProvider(): array
    {
        return [
            'contains parent > child descendant identifier' => [
                'resolvable' => new Resolvable('method(\'$"{{ $".parent" }} .child"\')', []),
                'expectedResolvedTemplate' => 'method(\'$"{{ $".parent" }} .child"\')',
            ],
            'contains grandparent > parent > child descendant identifier' => [
                'resolvable' => new Resolvable('method(\'$"{{ $"{{ $".grandparent" }} .parent" }} .child"\')', []),
                'expectedResolvedTemplate' => 'method(\'$"{{ $"{{ $".grandparent" }} .parent" }} .child"\')',
            ],
        ];
    }
}
