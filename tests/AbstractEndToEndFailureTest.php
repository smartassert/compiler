<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests;

use Symfony\Component\Yaml\Yaml;
use webignition\BaseBasilTestCase\AbstractBaseTest;
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
use webignition\BasilCompilerModels\Configuration;
use webignition\BasilCompilerModels\ErrorOutput;

abstract class AbstractEndToEndFailureTest extends AbstractEndToEndTest
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

    protected function replaceConfigurationPlaceholdersInString(string $value): string
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
    protected function replaceConfigurationPlaceholders(array $data): array
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
