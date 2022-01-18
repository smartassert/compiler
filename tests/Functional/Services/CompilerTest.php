<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Functional\Services;

use SmartAssert\Compiler\Model\CompiledTest;
use SmartAssert\Compiler\Services\Compiler;
use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCompilableSourceFactory\ClassDefinitionFactory;
use webignition\BasilCompilableSourceFactory\ClassNameFactory;
use webignition\BasilModels\Test\TestInterface;
use webignition\BasilParser\Test\TestParser;
use webignition\ObjectReflector\ObjectReflector;

class CompilerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider compileDataProvider
     *
     * @param string[] $classNameFactoryClassNames
     */
    public function testCompile(
        TestInterface $test,
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
    public function compileDataProvider(): array
    {
        $testParser = TestParser::create();
        $test = $testParser->parse(
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
        )->withPath('tests/Fixtures/basil/Test/example.com.verify-open-literal.yml');

        return [
            'default' => [
                'test' => $test,
                'classNameFactoryClassNames' => [
                    'GeneratedVerifyOpenLiteralChrome',
                ],
                'fullyQualifiedBaseClass' => AbstractBaseTest::class,
                'expectedCompiledTest' => new CompiledTest(
                    $this->createExpectedCodeFromSource(
                        'tests/Fixtures/php/Test/GeneratedVerifyOpenLiteralChrome.php'
                    ),
                    'GeneratedVerifyOpenLiteralChrome'
                ),
            ],
        ];
    }

    private function createExpectedCodeFromSource(string $source): string
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
