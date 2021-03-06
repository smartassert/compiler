<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests;

use SmartAssert\Compiler\Tests\DataProvider\FixturePaths;
use SmartAssert\Compiler\Tests\Model\CliArguments;
use SmartAssert\Compiler\Tests\Model\ExpectedGeneratedTest;
use SmartAssert\Compiler\Tests\Model\ExpectedGeneratedTestCollection;
use SmartAssert\Compiler\Tests\Services\ClassNameReplacer;
use Symfony\Component\Yaml\Yaml;
use webignition\BasilCompilerModels\Factory\TestManifestCollectionFactory;
use webignition\BasilCompilerModels\Factory\TestManifestFactory;

abstract class AbstractEndToEndSuccessTest extends AbstractEndToEndTest
{
    private ClassNameReplacer $classNameReplacer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classNameReplacer = new ClassNameReplacer();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $directoryIterator = new \DirectoryIterator(FixturePaths::getTarget());

        foreach ($directoryIterator as $item) {
            /** @var \DirectoryIterator $item */
            if ('php' === $item->getExtension() && $item->isFile() && $item->isWritable()) {
                unlink($item->getPathname());
            }
        }
    }

    /**
     * @dataProvider generateDataProvider
     *
     * @param array<string, string[]> $expectedStepNames
     */
    public function testGenerate(
        string $sourceRelativePath,
        ExpectedGeneratedTestCollection $expectedGeneratedTests,
        array $expectedStepNames,
    ): void {
        $cliArguments = new CliArguments(
            $this->getRemoteSourcePrefix() . $sourceRelativePath,
            $this->getRemoteTarget(),
        );

        $compilationOutput = $this->getCompilationOutput($cliArguments);
        $this->assertSame(0, $compilationOutput->getExitCode());

        self::assertSame('', $compilationOutput->getErrorContent());

        $outputContent = trim($compilationOutput->getOutputContent());
        $outputData = Yaml::parse($outputContent);
        self::assertIsArray($outputData);

        $testManifestCollectionFactory = new TestManifestCollectionFactory(
            new TestManifestFactory()
        );

        $testManifestCollection = $testManifestCollectionFactory->create($outputData);

        $localTarget = getcwd() . FixturePaths::TARGET;

        foreach ($testManifestCollection->getManifests() as $index => $testManifest) {
            $testPath = $testManifest->getTarget();
            $localTestPath = str_replace($cliArguments->getTarget(), $localTarget, $testPath);
            self::assertFileExists($localTestPath);

            $expectedGeneratedTest = $expectedGeneratedTests[$index];
            $generatedTestContent = (string) file_get_contents($localTestPath);

            $generatedTestContent = $this->classNameReplacer->replaceNamesInContent(
                $generatedTestContent,
                [$expectedGeneratedTest->getReplacementClassName()]
            );

            $this->assertSame($expectedGeneratedTest->getExpectedContent(), $generatedTestContent);

            $stepNames = $testManifest->getStepNames();
            self::assertIsArray($stepNames);

            $expectedManifestStepNamesKey = $sourceRelativePath . '.' . $testManifest->getBrowser();
            $expectedManifestStepNames = $expectedStepNames[$expectedManifestStepNamesKey];

            self::assertSame($expectedManifestStepNames, $stepNames);
        }
    }

    /**
     * @return array<mixed>
     */
    public function generateDataProvider(): array
    {
        return [
            'single test' => [
                'sourceRelativePath' => '/Test/example.com.verify-open-literal.yml',
                'expectedGeneratedTests' => new ExpectedGeneratedTestCollection([
                    new ExpectedGeneratedTest(
                        'GeneratedVerifyOpenLiteralChrome',
                        '/tests/Fixtures/php/Test/GeneratedVerifyOpenLiteralChrome.php',
                    ),
                ]),
                'expectedStepNames' => [
                    '/Test/example.com.verify-open-literal.yml.chrome' => [
                        'verify page is open',
                    ],
                ],
            ],
            'single test, verify open literal with page import' => [
                'sourceRelativePath' => '/Test/example.com.import-page.yml',
                'expectedGeneratedTests' => new ExpectedGeneratedTestCollection([
                    new ExpectedGeneratedTest(
                        'GeneratedImportPage',
                        '/tests/Fixtures/php/Test/GeneratedImportPage.php',
                    ),
                ]),
                'expectedStepNames' => [
                    '/Test/example.com.import-page.yml.chrome' => [
                        'verify page is open',
                    ],
                ],
            ],
            'single test with multiple browsers' => [
                '/Test/example.com.verify-open-literal-multiple-browsers.yml',
                'expectedGeneratedTests' => new ExpectedGeneratedTestCollection([
                    new ExpectedGeneratedTest(
                        'GeneratedVerifyOpenLiteralChrome',
                        '/tests/Fixtures/php/Test/GeneratedVerifyOpenLiteralChrome.php',
                    ),
                    new ExpectedGeneratedTest(
                        'GeneratedVerifyOpenLiteralFirefox',
                        '/tests/Fixtures/php/Test/GeneratedVerifyOpenLiteralFirefox.php',
                    ),
                ]),
                'expectedStepNames' => [
                    '/Test/example.com.verify-open-literal-multiple-browsers.yml.chrome' => [
                        'verify page is open',
                    ],
                    '/Test/example.com.verify-open-literal-multiple-browsers.yml.firefox' => [
                        'verify page is open',
                    ],
                ],
            ],
        ];
    }
}
