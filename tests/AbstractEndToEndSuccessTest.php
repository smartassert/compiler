<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests;

use SmartAssert\Compiler\Tests\DataProvider\FixturePaths;
use SmartAssert\Compiler\Tests\Model\CliArguments;
use SmartAssert\Compiler\Tests\Model\ExpectedGeneratedTest;
use SmartAssert\Compiler\Tests\Model\ExpectedGeneratedTestCollection;
use SmartAssert\Compiler\Tests\Services\ClassNameReplacer;
use Symfony\Component\Yaml\Yaml;
use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCompilerModels\SuiteManifest;

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
     */
    public function testGenerate(
        string $sourceRelativePath,
        ExpectedGeneratedTestCollection $expectedGeneratedTests
    ): void {
        $cliArguments = new CliArguments(
            $this->getRemoteSourcePrefix() . $sourceRelativePath,
            $this->getRemoteTarget(),
        );

        $compilationOutput = $this->getCompilationOutput($cliArguments);
        $this->assertSame(0, $compilationOutput->getExitCode());

        $suiteManifest = SuiteManifest::fromArray((array) Yaml::parse($compilationOutput->getContent()));

        $suiteManifestConfiguration = $suiteManifest->getConfiguration();
        self::assertSame($cliArguments->getSource(), $suiteManifestConfiguration->getSource());
        self::assertSame($cliArguments->getTarget(), $suiteManifestConfiguration->getTarget());
        self::assertSame(AbstractBaseTest::class, $suiteManifestConfiguration->getBaseClass());

        $testManifests = $suiteManifest->getTestManifests();
        self::assertCount(count($expectedGeneratedTests), $testManifests);

        $localTarget = getcwd() . FixturePaths::TARGET;

        foreach ($testManifests as $index => $testManifest) {
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
            ],
            'single test, verify open literal with page import' => [
                'sourceRelativePath' => '/Test/example.com.import-page.yml',
                'expectedGeneratedTests' => new ExpectedGeneratedTestCollection([
                    new ExpectedGeneratedTest(
                        'GeneratedImportPage',
                        '/tests/Fixtures/php/Test/GeneratedImportPage.php',
                    ),
                ]),
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
            ],
        ];
    }
}
