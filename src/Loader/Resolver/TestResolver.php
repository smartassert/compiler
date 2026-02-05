<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Resolver;

use webignition\BasilModels\Model\Step\StepCollection;
use webignition\BasilModels\Model\Test\Test;
use webignition\BasilModels\Model\Test\TestInterface;
use webignition\BasilModels\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\BasilModels\Provider\Page\PageProviderInterface;
use webignition\BasilModels\Provider\Step\StepProviderInterface;

class TestResolver
{
    public function __construct(
        private readonly ImportedUrlResolver $importedUrlResolver,
        private readonly StepResolver $stepResolver,
        private readonly StepImportResolver $stepImportResolver
    ) {}

    public static function createResolver(): TestResolver
    {
        return new TestResolver(
            ImportedUrlResolver::createResolver(),
            StepResolver::createResolver(),
            StepImportResolver::createResolver()
        );
    }

    /**
     * @throws CircularStepImportException
     * @throws UnknownElementException
     * @throws UnknownItemException
     * @throws UnknownPageElementException
     */
    public function resolve(
        TestInterface $test,
        PageProviderInterface $pageProvider,
        StepProviderInterface $stepProvider,
        DataSetProviderInterface $dataSetProvider
    ): TestInterface {
        $resolvedSteps = [];
        foreach ($test->getSteps() as $stepName => $step) {
            try {
                $resolvedStep = $this->stepImportResolver->resolveStepImport($step, $stepProvider);
                $resolvedStep = $this->stepImportResolver->resolveDataProviderImport(
                    $resolvedStep,
                    $dataSetProvider
                );
                $resolvedStep = $this->stepResolver->resolve($resolvedStep, $pageProvider);
                $resolvedStep = $resolvedStep->withIdentifiers([]);

                $resolvedSteps[$stepName] = $resolvedStep;
            } catch (UnknownElementException | UnknownItemException | UnknownPageElementException $exception) {
                $exception->setStepName((string) $stepName);

                throw $exception;
            }
        }

        return new Test(
            $test->getBrowser(),
            $this->importedUrlResolver->resolve($test->getUrl(), $pageProvider),
            new StepCollection($resolvedSteps)
        );
    }
}
