<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Services;

use SmartAssert\Compiler\Services\CompiledClassResolver;
use SmartAssert\Compiler\Services\ExternalVariableIdentifiersFactory;
use webignition\BasilCompilableSourceFactory\Model\VariableDependency;
use webignition\BasilCompilableSourceFactory\VariableNames;
use webignition\Stubble\VariableResolver;
use webignition\StubbleResolvable\ResolvableCollection;
use webignition\StubbleResolvable\ResolvedTemplateMutatorResolvable;

class CompiledClassResolverTest extends \PHPUnit\Framework\TestCase
{
    private CompiledClassResolver $compiledClassResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiledClassResolver = CompiledClassResolver::createResolver(
            ExternalVariableIdentifiersFactory::create()
        );
    }

    /**
     * @dataProvider resolveDataProvider
     */
    public function testResolve(string $compiledClass, string $expectedResolvedClass): void
    {
        $resolvedContent = $this->compiledClassResolver->resolve($compiledClass);

        $this->assertSame($expectedResolvedClass, $resolvedContent);
    }

    /**
     * @return array<mixed>
     */
    public function resolveDataProvider(): array
    {
        return [
            'empty content' => [
                'content' => '',
                'expectedResolvedContent' => '',
            ],
            'non-resolvable content' => [
                'content' => 'non-resolvable content',
                'expectedResolvedContent' => 'non-resolvable content',
            ],
            'resolvable content' => [
                'content' => $this->createRenderedListOfAllExternalDependencies(),
                'expectedResolvedContent' => '$this->actionFactory' . "\n" .
                    '$this->assertionFactory' . "\n" .
                    '$this->navigator' . "\n" .
                    '$_ENV' . "\n" .
                    'self::$client' . "\n" .
                    'self::$crawler' . "\n" .
                    'self::$inspector' . "\n" .
                    'self::$mutator',
            ],
        ];
    }

    private function createRenderedListOfAllExternalDependencies(): string
    {
        $variableDependencies = [
            new VariableDependency(VariableNames::ACTION_FACTORY),
            new VariableDependency(VariableNames::ASSERTION_FACTORY),
            new VariableDependency(VariableNames::DOM_CRAWLER_NAVIGATOR),
            new VariableDependency(VariableNames::ENVIRONMENT_VARIABLE_ARRAY),
            new VariableDependency(VariableNames::PANTHER_CLIENT),
            new VariableDependency(VariableNames::PANTHER_CRAWLER),
            new VariableDependency(VariableNames::WEBDRIVER_ELEMENT_INSPECTOR),
            new VariableDependency(VariableNames::WEBDRIVER_ELEMENT_MUTATOR),
        ];

        $mutableVariableDependencies = [];
        foreach ($variableDependencies as $variableDependency) {
            $mutableVariableDependencies[] = new ResolvedTemplateMutatorResolvable(
                $variableDependency,
                function (string $resolvedTemplate) {
                    return $resolvedTemplate . "\n";
                },
            );
        }

        return (new VariableResolver())->resolveAndIgnoreUnresolvedVariables(
            new ResolvedTemplateMutatorResolvable(
                ResolvableCollection::create($mutableVariableDependencies),
                function (string $resolvedTemplate) {
                    return trim($resolvedTemplate);
                }
            )
        );
    }
}
