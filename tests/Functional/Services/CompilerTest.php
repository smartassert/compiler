<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Functional\Services;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Model\CompiledTest;
use SmartAssert\Compiler\Services\Compiler;
use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCompilableSourceFactory\ClassDefinitionFactory;
use webignition\BasilCompilableSourceFactory\ClassNameFactory;
use webignition\BasilModels\Model\Test\NamedTest;
use webignition\BasilModels\Model\Test\NamedTestInterface;
use webignition\BasilModels\Parser\Test\TestParser;
use webignition\ObjectReflector\ObjectReflector;

class CompilerTest extends TestCase
{
    /**
     * @param non-empty-string $fullyQualifiedBaseClass
     *
     * @param string[] $classNameFactoryClassNames
     */
    #[DataProvider('compileDataProvider')]
    public function testCompile(
        NamedTestInterface $test,
        array $classNameFactoryClassNames,
        string $fullyQualifiedBaseClass,
        CompiledTest $expectedCompiledTest
    ): void {
        $compiler = Compiler::createCompiler();
        $compiler = $this->mockClassNameFactoryOnCompiler($compiler, $classNameFactoryClassNames);

        self::assertEquals(
            $expectedCompiledTest,
            $compiler->compile($test, $fullyQualifiedBaseClass)
        );
    }

    /**
     * @return array<mixed>
     */
    public static function compileDataProvider(): array
    {
        $test = new NamedTest(
            TestParser::create()->parse(
                [
                    'config' => [
                        'browser' => 'chrome',
                        'url' => 'https://example.com/',
                    ],
                    'verify page is open' => [
                        'assertions' => [
                            '$page.url is "https://example.com/"',
                        ],
                    ],
                ]
            ),
            'tests/Fixtures/basil/Test/example.com.verify-open-literal.yml'
        );

        return [
            'default' => [
                'test' => $test,
                'classNameFactoryClassNames' => [
                    'GeneratedVerifyOpenLiteralChrome',
                ],
                'fullyQualifiedBaseClass' => AbstractBaseTest::class,
                'expectedCompiledTest' => new CompiledTest(
                    self::createExpectedCodeFromSource(
                        'tests/Fixtures/php/Test/GeneratedVerifyOpenLiteralChrome.php'
                    ),
                    'GeneratedVerifyOpenLiteralChrome'
                ),
            ],
        ];
    }

    private static function createExpectedCodeFromSource(string $source): string
    {
        $content = (string) file_get_contents($source);

        $contentLines = explode("\n", $content);
        array_shift($contentLines);
        array_shift($contentLines);
        array_shift($contentLines);
        array_shift($contentLines);

        return trim(implode("\n", $contentLines));
    }

    /**
     * @param string[] $classNames
     */
    private function mockClassNameFactoryOnCompiler(Compiler $compiler, array $classNames): Compiler
    {
        $classDefinitionFactory = ObjectReflector::getProperty($compiler, 'classDefinitionFactory');
        \assert($classDefinitionFactory instanceof ClassDefinitionFactory);

        $classNameFactory = \Mockery::mock(ClassNameFactory::class);
        $classNameFactory
            ->shouldReceive('create')
            ->andReturnValues($classNames)
        ;

        ObjectReflector::setProperty(
            $classDefinitionFactory,
            ClassDefinitionFactory::class,
            'classNameFactory',
            $classNameFactory
        );

        ObjectReflector::setProperty(
            $compiler,
            Compiler::class,
            'classDefinitionFactory',
            $classDefinitionFactory
        );

        return $compiler;
    }
}
