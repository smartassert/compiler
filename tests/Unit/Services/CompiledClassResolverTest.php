<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Services;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Services\CompiledClassResolver;
use SmartAssert\Compiler\Services\DependencyVariablesFactory;
use webignition\BasilCompilableSourceFactory\Enum\DependencyName;
use webignition\BasilCompilableSourceFactory\Model\Property;
use webignition\Stubble\Resolvable\ResolvableCollection;
use webignition\Stubble\Resolvable\ResolvedTemplateMutatorResolvable;
use webignition\Stubble\VariableResolver;

class CompiledClassResolverTest extends TestCase
{
    private CompiledClassResolver $compiledClassResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiledClassResolver = CompiledClassResolver::createResolver(
            DependencyVariablesFactory::create()
        );
    }

    #[DataProvider('resolveDataProvider')]
    public function testResolve(string $compiledClass, string $expectedResolvedClass): void
    {
        $resolvedContent = $this->compiledClassResolver->resolve($compiledClass);

        $this->assertSame($expectedResolvedClass, $resolvedContent);
    }

    /**
     * @return array<mixed>
     */
    public static function resolveDataProvider(): array
    {
        return [
            'empty content' => [
                'compiledClass' => '',
                'expectedResolvedClass' => '',
            ],
            'non-resolvable content' => [
                'compiledClass' => 'non-resolvable content',
                'expectedResolvedClass' => 'non-resolvable content',
            ],
            'resolvable content' => [
                'compiledClass' => self::createRenderedListOfAllExternalDependencies(),
                'expectedResolvedClass' => <<<'EOD'
                    $this->navigator
                    $_ENV
                    self::$client
                    self::$crawler
                    self::$inspector
                    self::$mutator
                    EOD,
            ],
        ];
    }

    private static function createRenderedListOfAllExternalDependencies(): string
    {
        $variableDependencies = [
            Property::asDependency(DependencyName::DOM_CRAWLER_NAVIGATOR),
            Property::asDependency(DependencyName::ENVIRONMENT_VARIABLE_ARRAY),
            Property::asDependency(DependencyName::PANTHER_CLIENT),
            Property::asDependency(DependencyName::PANTHER_CRAWLER),
            Property::asDependency(DependencyName::WEBDRIVER_ELEMENT_INSPECTOR),
            Property::asDependency(DependencyName::WEBDRIVER_ELEMENT_MUTATOR),
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

        return new VariableResolver()->resolveAndIgnoreUnresolvedVariables(
            new ResolvedTemplateMutatorResolvable(
                ResolvableCollection::create($mutableVariableDependencies),
                function (string $resolvedTemplate) {
                    return trim($resolvedTemplate);
                }
            )
        );
    }
}
