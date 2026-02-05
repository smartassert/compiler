<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader;

use SmartAssert\Compiler\Loader\Exception\YamlLoaderException;
use webignition\BasilModels\Model\Step\StepInterface;
use webignition\BasilModels\Parser\Exception\UnparseableStepException;
use webignition\BasilModels\Parser\StepParser;

class StepLoader
{
    public function __construct(
        private YamlLoader $yamlLoader,
        private StepParser $stepParser
    ) {}

    public static function createLoader(): StepLoader
    {
        return new StepLoader(
            YamlLoader::createLoader(),
            StepParser::create()
        );
    }

    /**
     * @throws UnparseableStepException
     * @throws YamlLoaderException
     */
    public function load(string $path): StepInterface
    {
        $data = $this->yamlLoader->loadArray($path);

        return $this->stepParser->parse($data);
    }
}
