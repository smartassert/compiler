<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\Integration\Bin;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use webignition\BasilCliCompiler\Tests\DataProvider\FixturePaths;
use webignition\BasilCliCompiler\Tests\Model\CliArguments;
use webignition\BasilCliCompiler\Tests\Model\CompilationOutput;
use webignition\BasilCliCompiler\Tests\Model\ExpectedGeneratedTest;
use webignition\BasilCliCompiler\Tests\Model\ExpectedGeneratedTestCollection;
use webignition\BasilCliCompiler\Tests\Services\ClassNameReplacer;
use webignition\BasilCompilerModels\SuiteManifest;

class CompilerTest extends TestCase
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
        CliArguments $cliArguments,
        string $localTarget,
        ExpectedGeneratedTestCollection $expectedGeneratedTests
    ): void {
        $compilationOutput = $this->getCompilationOutput($cliArguments);
        $this->assertSame(0, $compilationOutput->getExitCode());

        $suiteManifest = SuiteManifest::fromArray((array) Yaml::parse($compilationOutput->getContent()));

        $testManifests = $suiteManifest->getTestManifests();
        self::assertNotEmpty($testManifests);

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
            $generatedTestContent = $this->removeProjectRootPathInGeneratedTest($generatedTestContent);

            $this->assertSame($expectedGeneratedTest->getExpectedContent(), $generatedTestContent);
        }
    }

    /**
     * @return array[]
     */
    public function generateDataProvider(): array
    {
        $root = getcwd();

        return [
            'single test' => [
                'cliArguments' => new CliArguments(
                    $root . '/tests/Fixtures/basil/Test/example.com.verify-open-literal.yml',
                    $root . '/tests/build/target',
                ),
                'localTarget' => $root . '/tests/build/target',
                'expectedGeneratedTests' => new ExpectedGeneratedTestCollection([
                    new ExpectedGeneratedTest(
                        'GeneratedVerifyOpenLiteralChrome',
                        '/tests/Fixtures/php/Test/GeneratedVerifyOpenLiteralChrome.php',
                    ),
                ]),
            ],
        ];
    }

    private function removeProjectRootPathInGeneratedTest(string $generatedTestContent): string
    {
        return str_replace((string) getcwd(), '', $generatedTestContent);
    }

    private function getCompilationOutput(CliArguments $cliArguments): CompilationOutput
    {
        $processArguments = array_merge(['./bin/compiler'], $cliArguments->toArgvArray());
        $process = new Process($processArguments);
        $exitCode = $process->run();

        return new CompilationOutput($process->getOutput(), $exitCode);
    }
}
