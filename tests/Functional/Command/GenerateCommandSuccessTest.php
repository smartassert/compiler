<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\Functional\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Yaml\Yaml;
use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCliCompiler\Services\CommandFactory;
use webignition\BasilCliCompiler\Tests\DataProvider\FixturePaths;
use webignition\BasilCliCompiler\Tests\DataProvider\RunSuccess\SuccessDataProviderTrait;
use webignition\BasilCliCompiler\Tests\Model\CliArguments;
use webignition\BasilCliCompiler\Tests\Model\ExpectedGeneratedTestCollection;
use webignition\BasilCliCompiler\Tests\Services\ClassNameReplacer;
use webignition\BasilCompilerModels\SuiteManifest;

class GenerateCommandSuccessTest extends TestCase
{
    use SuccessDataProviderTrait;

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
     * @dataProvider successDataProvider
     */
    public function testRunSuccess(
        CliArguments $cliArguments,
        ExpectedGeneratedTestCollection $expectedGeneratedTests
    ): void {
        $stdout = new BufferedOutput();
        $stderr = new BufferedOutput();

        $command = CommandFactory::createGenerateCommand($stdout, $stderr, $cliArguments->toArgvArray());

        $exitCode = $command->run(new ArrayInput($cliArguments->getOptions()), new NullOutput());
        self::assertSame(0, $exitCode);
        self::assertSame('', $stderr->fetch());

        $output = $stdout->fetch();

        $suiteManifest = SuiteManifest::fromArray((array) Yaml::parse($output));

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
            $generatedTestContent = $this->removeProjectRootPathInGeneratedTest($generatedTestContent);

            $this->assertSame($expectedGeneratedTest->getExpectedContent(), $generatedTestContent);
        }
    }

    private function removeProjectRootPathInGeneratedTest(string $generatedTestContent): string
    {
        return str_replace((string) getcwd(), '', $generatedTestContent);
    }
}
