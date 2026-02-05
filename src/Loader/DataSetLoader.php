<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader;

use SmartAssert\Compiler\Loader\Exception\YamlLoaderException;
use webignition\BasilModels\Model\DataSet\DataSetCollection;
use webignition\BasilModels\Model\DataSet\DataSetCollectionInterface;

class DataSetLoader
{
    public function __construct(
        private YamlLoader $yamlLoader
    ) {}

    public static function createLoader(): DataSetLoader
    {
        return new DataSetLoader(
            YamlLoader::createLoader()
        );
    }

    /**
     * @throws YamlLoaderException
     */
    public function load(string $path): DataSetCollectionInterface
    {
        $data = $this->yamlLoader->loadArray($path);

        return new DataSetCollection($data);
    }
}
