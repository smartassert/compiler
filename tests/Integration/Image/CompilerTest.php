<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\Integration\Image;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use webignition\BasilCliCompiler\Tests\Model\CompilationOutput;
use webignition\BasilCliCompiler\Tests\Services\ClassNameReplacer;
use webignition\BasilCompilerModels\SuiteManifest;
use webignition\TcpCliProxyClient\Client;
use webignition\TcpCliProxyClient\HandlerFactory;

class CompilerTest extends TestCase
{
    private ClassNameReplacer $classNameReplacer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classNameReplacer = new ClassNameReplacer();
    }

    /**
     * @dataProvider generateDataProvider
     *
     * @param array<mixed> $expectedGeneratedTestDataCollection
     */
    public function testGenerate(
        string $sourceDirectory,
        string $sourcePath,
        string $remoteTarget,
        string $localTarget,
        array $expectedGeneratedTestDataCollection
    ): void {
        $compilationOutput = $this->getCompilationOutput($sourceDirectory . $sourcePath, $remoteTarget);
        $this->assertSame(0, $compilationOutput->getExitCode());

        $suiteManifest = SuiteManifest::fromArray((array) Yaml::parse($compilationOutput->getContent()));

        $testManifests = $suiteManifest->getTestManifests();
        self::assertNotEmpty($testManifests);

        foreach ($testManifests as $index => $testManifest) {
            $testPath = $testManifest->getTarget();
            $localTestPath = str_replace($remoteTarget, $localTarget, $testPath);
            self::assertFileExists($localTestPath);

            $expectedGeneratedTestData = $expectedGeneratedTestDataCollection[$index];

            $generatedTestContent = (string) file_get_contents($localTestPath);

            $classNameReplacement = $expectedGeneratedTestData['classNameReplacement'];
            $generatedTestContent = $this->classNameReplacer->replaceNamesInContent(
                $generatedTestContent,
                [$classNameReplacement]
            );
            $generatedTestContent = $this->removeProjectRootPathInGeneratedTest($generatedTestContent);

            $expectedTestContentPath = getcwd() . '/' . $expectedGeneratedTestData['expectedContentPath'];
            $expectedTestContent = (string) file_get_contents($expectedTestContentPath);

            $this->assertSame($expectedTestContent, $generatedTestContent);
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
                'sourceDirectory' => '/app/source',
                'sourcePath' => '/Test/example.com.verify-open-literal.yml',
                'remoteTarget' => '/app/tests',
                'localTarget' => $root . '/tests/build/target',
                'expectedGeneratedTestDataCollection' => [
                    [
                        'classNameReplacement' => 'GeneratedVerifyOpenLiteralChrome',
                        'expectedContentPath' => '/tests/Fixtures/php/Test/GeneratedVerifyOpenLiteralChrome.php',
                    ],
                ],
            ],
        ];
    }

    private function removeProjectRootPathInGeneratedTest(string $generatedTestContent): string
    {
        return str_replace((string) getcwd(), '', $generatedTestContent);
    }

    private function getCompilationOutput(string $source, string $remoteTarget): CompilationOutput
    {
        $output = '';
        $exitCode = 0;

        $handler = (new HandlerFactory())->createWithScalarOutput($output, $exitCode);

        $client = Client::createFromHostAndPort('localhost', 8000);

        $client->request(
            sprintf(
                './compiler --source=%s --target=%s',
                $source,
                $remoteTarget
            ),
            $handler
        );

        return new CompilationOutput($output, $exitCode);
    }
}
