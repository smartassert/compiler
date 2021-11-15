<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCliCompiler\Tests\DataProvider\FixturePaths;
use webignition\BasilCliCompiler\Tests\DataProvider\RunFailure\CircularStepImportDataProviderTrait;
use webignition\BasilCliCompiler\Tests\DataProvider\RunFailure\EmptyTestDataProviderTrait;
use webignition\BasilCliCompiler\Tests\DataProvider\RunFailure\InvalidPageDataProviderTrait;
use webignition\BasilCliCompiler\Tests\DataProvider\RunFailure\InvalidTestDataProviderTrait;
use webignition\BasilCliCompiler\Tests\DataProvider\RunFailure\NonLoadableDataDataProviderTrait;
use webignition\BasilCliCompiler\Tests\DataProvider\RunFailure\NonRetrievableImportDataProviderTrait;
use webignition\BasilCliCompiler\Tests\DataProvider\RunFailure\ParseExceptionDataProviderTrait;
use webignition\BasilCliCompiler\Tests\DataProvider\RunFailure\UnknownElementDataProviderTrait;
use webignition\BasilCliCompiler\Tests\DataProvider\RunFailure\UnknownItemDataProviderTrait;
use webignition\BasilCliCompiler\Tests\DataProvider\RunFailure\UnknownPageElementDataProviderTrait;
use webignition\BasilCliCompiler\Tests\Model\CliArguments;
use webignition\BasilCliCompiler\Tests\Model\CompilationOutput;
use webignition\BasilCompilerModels\Configuration;
use webignition\BasilCompilerModels\ErrorOutput;
use webignition\BasilCompilerModels\ErrorOutputInterface;
use webignition\BasilCompilerModels\SuiteManifest;
use webignition\TcpCliProxyClient\Client;
use webignition\TcpCliProxyClient\HandlerFactory;

class ImageFailureTest extends TestCase
{
    use NonLoadableDataDataProviderTrait;
    use CircularStepImportDataProviderTrait;
    use EmptyTestDataProviderTrait;
    use InvalidPageDataProviderTrait;
    use InvalidTestDataProviderTrait;
    use NonRetrievableImportDataProviderTrait;
    use ParseExceptionDataProviderTrait;
    use UnknownElementDataProviderTrait;
    use UnknownItemDataProviderTrait;
    use UnknownPageElementDataProviderTrait;

    /**
     * @dataProvider nonLoadableDataDataProvider
     * @dataProvider circularStepImportDataProvider
     * @dataProvider emptyTestDataProvider
     * @dataProvider invalidPageDataProvider
     * @dataProvider invalidTestDataProvider
     * @dataProvider nonRetrievableImportDataProvider
     * @dataProvider parseExceptionDataProvider
     * @dataProvider unknownElementDataProvider
     * @dataProvider unknownItemDataProvider
     * @dataProvider unknownPageElementDataProvider
     *
     * @param array<mixed> $expectedErrorOutputData
     */
    public function testGenerateFailure(
        string $sourceRelativePath,
        int $expectedExitCode,
        string $expectedErrorOutputMessage,
        int $expectedErrorOutputCode,
        array $expectedErrorOutputData,
    ): void {
        $cliArguments = new CliArguments(
            $this->getRemoteSourcePrefix() . $sourceRelativePath,
            $this->getRemoteTarget(),
        );

        $compilationOutput = $this->getCompilationOutput($cliArguments);
        self::assertSame($expectedExitCode, $compilationOutput->getExitCode());

        $output = $compilationOutput->getContent();

        $commandOutput = ErrorOutput::fromArray((array) Yaml::parse($output));
        $configuration = $commandOutput->getConfiguration();
        self::assertSame($cliArguments->getSource(), $configuration->getSource());
        self::assertSame($cliArguments->getTarget(), $configuration->getTarget());
        self::assertSame(AbstractBaseTest::class, $configuration->getBaseClass());

        $expectedErrorOutputData = $this->replaceConfigurationPlaceholders($expectedErrorOutputData);

        $expectedCommandOutput = new ErrorOutput(
            new Configuration(
                $cliArguments->getSource(),
                $cliArguments->getTarget(),
                AbstractBaseTest::class
            ),
            $this->replaceConfigurationPlaceholdersInString($expectedErrorOutputMessage),
            $expectedErrorOutputCode,
            $expectedErrorOutputData
        );

        self::assertEquals($expectedCommandOutput, $commandOutput);
    }

    protected function getRemoteSourcePrefix(): string
    {
        return '/app/source';
    }

    protected function getRemoteTarget(): string
    {
        return '/app/tests';
    }

    protected function getCompilationOutput(CliArguments $cliArguments): CompilationOutput
    {
        $output = '';
        $exitCode = 0;

        $handler = (new HandlerFactory())->createWithScalarOutput($output, $exitCode);

        $client = Client::createFromHostAndPort('localhost', 8000);
        $client->request('./compiler ' . $cliArguments, $handler);

        return new CompilationOutput($output, $exitCode);
    }

    private function replaceConfigurationPlaceholdersInString(string $value): string
    {
        return str_replace(
            [
                '{{ remoteSourcePrefix }}',
                '{{ remoteTarget }}',
            ],
            [
                $this->getRemoteSourcePrefix(),
                $this->getRemoteTarget(),
            ],
            $value
        );
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    private function replaceConfigurationPlaceholders(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = $this->replaceConfigurationPlaceholdersInString($value);
            }

            if (is_array($value)) {
                $data[$key] = $this->replaceConfigurationPlaceholders($value);
            }
        }

        return $data;
    }
}
