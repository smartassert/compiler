<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests;

use SmartAssert\Compiler\Tests\DataProvider\RunFailure\CircularStepImportDataProviderTrait;
use SmartAssert\Compiler\Tests\DataProvider\RunFailure\EmptyTestDataProviderTrait;
use SmartAssert\Compiler\Tests\DataProvider\RunFailure\InvalidPageDataProviderTrait;
use SmartAssert\Compiler\Tests\DataProvider\RunFailure\InvalidTestDataProviderTrait;
use SmartAssert\Compiler\Tests\DataProvider\RunFailure\NonLoadableDataDataProviderTrait;
use SmartAssert\Compiler\Tests\DataProvider\RunFailure\NonRetrievableImportDataProviderTrait;
use SmartAssert\Compiler\Tests\DataProvider\RunFailure\ParseExceptionDataProviderTrait;
use SmartAssert\Compiler\Tests\DataProvider\RunFailure\UnknownElementDataProviderTrait;
use SmartAssert\Compiler\Tests\DataProvider\RunFailure\UnknownItemDataProviderTrait;
use SmartAssert\Compiler\Tests\DataProvider\RunFailure\UnknownPageElementDataProviderTrait;
use SmartAssert\Compiler\Tests\Model\CliArguments;
use webignition\BasilCompilerModels\Factory\ErrorOutputFactory;
use webignition\BasilCompilerModels\Model\ErrorOutput;
use webignition\YamlDocument\Document;

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

        $outputContent = trim($compilationOutput->getOutputContent());
        self::assertSame('', $outputContent);

        $errorContent = trim($compilationOutput->getErrorContent());
        $errorDocuments = $this->processYamlCollectionOutput($errorContent);
        self::assertCount(1, $errorDocuments);

        $errorDocument = $errorDocuments[0];
        self::assertInstanceOf(Document::class, $errorDocument);

        $expectedErrorOutputData = $this->replaceConfigurationPlaceholders($expectedErrorOutputData);

        $expectedCommandOutput = new ErrorOutput(
            $this->replaceConfigurationPlaceholdersInString($expectedErrorOutputMessage),
            $expectedErrorOutputCode,
            $expectedErrorOutputData
        );

        $errorOutput = (new ErrorOutputFactory())->create((array) $errorDocument->parse());
        self::assertEquals($expectedCommandOutput, $errorOutput);
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
