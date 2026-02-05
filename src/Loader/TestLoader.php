<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader;

use SmartAssert\Compiler\Loader\Exception\EmptyTestException;
use SmartAssert\Compiler\Loader\Exception\InvalidPageException;
use SmartAssert\Compiler\Loader\Exception\InvalidTestException;
use SmartAssert\Compiler\Loader\Exception\NonRetrievableImportException;
use SmartAssert\Compiler\Loader\Exception\ParseException;
use SmartAssert\Compiler\Loader\Exception\YamlLoaderException;
use SmartAssert\Compiler\Loader\Resolver\CircularStepImportException;
use SmartAssert\Compiler\Loader\Resolver\TestResolver;
use SmartAssert\Compiler\Loader\Resolver\UnknownElementException;
use SmartAssert\Compiler\Loader\Resolver\UnknownPageElementException;
use SmartAssert\Compiler\Loader\Validator\InvalidResult;
use SmartAssert\Compiler\Loader\Validator\InvalidResultInterface;
use SmartAssert\Compiler\Loader\Validator\ResultType;
use SmartAssert\Compiler\Loader\Validator\Test\TestValidator;
use webignition\BasilModels\Model\Test\NamedTest;
use webignition\BasilModels\Model\Test\NamedTestInterface;
use webignition\BasilModels\Parser\Exception\InvalidTestException as ParserInvalidTestException;
use webignition\BasilModels\Parser\Exception\UnparseableStepException;
use webignition\BasilModels\Parser\Exception\UnparseableTestException;
use webignition\BasilModels\Parser\Test\ImportsParser;
use webignition\BasilModels\Parser\Test\TestParser;
use webignition\BasilModels\Provider\DataSet\DataSetProvider;
use webignition\BasilModels\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\BasilModels\Provider\Page\PageProvider;
use webignition\BasilModels\Provider\Page\PageProviderInterface;
use webignition\BasilModels\Provider\Step\StepProvider;
use webignition\BasilModels\Provider\Step\StepProviderInterface;

class TestLoader
{
    private const DATA_KEY_IMPORTS = 'imports';

    public function __construct(
        private readonly YamlLoader $yamlLoader,
        private readonly DataSetLoader $dataSetLoader,
        private readonly PageLoader $pageLoader,
        private readonly StepLoader $stepLoader,
        private readonly TestResolver $testResolver,
        private readonly TestParser $testParser,
        private readonly TestValidator $testValidator,
        private readonly ImportsParser $importsParser
    ) {}

    public static function createLoader(): TestLoader
    {
        return new TestLoader(
            YamlLoader::createLoader(),
            DataSetLoader::createLoader(),
            PageLoader::createLoader(),
            StepLoader::createLoader(),
            TestResolver::createResolver(),
            TestParser::create(),
            TestValidator::create(),
            ImportsParser::create()
        );
    }

    /**
     * @param non-empty-string $path
     *
     * @return NamedTestInterface[]
     *
     * @throws CircularStepImportException
     * @throws EmptyTestException
     * @throws InvalidPageException
     * @throws InvalidTestException
     * @throws NonRetrievableImportException
     * @throws ParseException
     * @throws UnknownElementException
     * @throws UnknownItemException
     * @throws UnknownPageElementException
     * @throws YamlLoaderException
     */
    public function load(string $path): array
    {
        $data = $this->yamlLoader->loadArray($path);
        if ([] === $data) {
            throw new EmptyTestException($path);
        }

        $singleBrowserDataSets = $this->createSingleBrowserDataSets($data);
        if ([] === $singleBrowserDataSets) {
            $singleBrowserDataSets = [$data];
        }

        $tests = [];

        foreach ($singleBrowserDataSets as $data) {
            $tests[] = $this->createTest($path, $data);
        }

        return $tests;
    }

    /**
     * @param non-empty-string $path
     * @param array<mixed>     $data
     *
     * @throws CircularStepImportException
     * @throws InvalidPageException
     * @throws InvalidTestException
     * @throws NonRetrievableImportException
     * @throws ParseException
     * @throws UnknownElementException
     * @throws UnknownItemException
     * @throws UnknownPageElementException
     */
    private function createTest(string $path, array $data): NamedTestInterface
    {
        $basePath = dirname($path) . '/';

        try {
            $test = $this->testParser->parse($data);
        } catch (UnparseableTestException $unparseableTestException) {
            throw new ParseException($path, $path, $unparseableTestException);
        } catch (ParserInvalidTestException $e) {
            $invalidResultReason = ParserInvalidTestException::CODE_BROWSER_EMPTY === $e->getCode()
                ? TestValidator::REASON_BROWSER_EMPTY
                : TestValidator::REASON_URL_EMPTY;

            $invalidResult = new InvalidResult(
                [
                    'path' => $path,
                    'data' => $data,
                ],
                ResultType::TEST,
                $invalidResultReason
            );

            throw new InvalidTestException($path, $invalidResult);
        }

        $importsData = $data[self::DATA_KEY_IMPORTS] ?? [];
        $importsData = is_array($importsData) ? $importsData : [];

        $imports = $this->importsParser->parse($basePath, $importsData);

        try {
            $pageProvider = $this->createPageProvider($imports->getPagePaths());
            $stepProvider = $this->createStepProvider($path, $imports->getStepPaths());
            $dataSetProvider = $this->createDataSetProvider($imports->getDataProviderPaths());
        } catch (NonRetrievableImportException $nonRetrievableImportException) {
            $nonRetrievableImportException->setTestPath($path);

            throw $nonRetrievableImportException;
        } catch (InvalidPageException $invalidPageException) {
            $invalidPageException->setTestPath($path);

            throw $invalidPageException;
        }

        try {
            $resolvedTest = $this->testResolver->resolve($test, $pageProvider, $stepProvider, $dataSetProvider);
        } catch (UnknownElementException | UnknownItemException | UnknownPageElementException $exception) {
            $exception->setTestName($path);

            throw $exception;
        }

        $validationResult = $this->testValidator->validate($resolvedTest);
        if ($validationResult instanceof InvalidResultInterface) {
            throw new InvalidTestException($path, $validationResult);
        }

        return new NamedTest($resolvedTest, $path);
    }

    /**
     * @param array<string, string> $importPaths
     *
     * @throws NonRetrievableImportException
     */
    private function createDataSetProvider(array $importPaths): DataSetProviderInterface
    {
        $dataSetCollections = [];

        foreach ($importPaths as $name => $path) {
            try {
                $dataSetCollections[$name] = $this->dataSetLoader->load($path);
            } catch (YamlLoaderException $yamlLoaderException) {
                throw new NonRetrievableImportException(
                    NonRetrievableImportException::TYPE_DATA_PROVIDER,
                    $name,
                    $path,
                    $yamlLoaderException
                );
            }
        }

        return new DataSetProvider($dataSetCollections);
    }

    /**
     * @param array<string, string> $importPaths
     *
     * @throws InvalidPageException
     * @throws NonRetrievableImportException
     */
    private function createPageProvider(array $importPaths): PageProviderInterface
    {
        $pages = [];

        foreach ($importPaths as $name => $path) {
            try {
                $pages[$name] = $this->pageLoader->load($name, $path);
            } catch (YamlLoaderException $yamlLoaderException) {
                throw new NonRetrievableImportException(
                    NonRetrievableImportException::TYPE_PAGE,
                    $name,
                    $path,
                    $yamlLoaderException
                );
            }
        }

        return new PageProvider($pages);
    }

    /**
     * @param array<string, string> $importPaths
     *
     * @throws NonRetrievableImportException
     * @throws ParseException
     */
    private function createStepProvider(string $testPath, array $importPaths): StepProviderInterface
    {
        $steps = [];

        foreach ($importPaths as $name => $path) {
            try {
                $steps[$name] = $this->stepLoader->load($path);
            } catch (YamlLoaderException $yamlLoaderException) {
                throw new NonRetrievableImportException(
                    NonRetrievableImportException::TYPE_STEP,
                    $name,
                    $path,
                    $yamlLoaderException
                );
            } catch (UnparseableStepException $unparseableStepException) {
                throw new ParseException($testPath, $path, $unparseableStepException);
            }
        }

        return new StepProvider($steps);
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<array<mixed>>
     */
    private function createSingleBrowserDataSets(array $data): array
    {
        $configData = $data['config'] ?? [];
        $configData = is_array($configData) ? $configData : [];

        $browsers = $configData['browsers'] ?? [];
        $browsers = is_array($browsers) ? $browsers : [];

        $url = $configData['url'] ?? '';

        $browserSpecificDataSets = [];
        foreach ($browsers as $browser) {
            $browserSpecificData = $data;
            $browserSpecificData['config'] = [
                'browser' => $browser,
                'url' => $url,
            ];

            $browserSpecificDataSets[] = $browserSpecificData;
        }

        return $browserSpecificDataSets;
    }
}
