<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Validator;

use webignition\BasilModels\Model\DataParameter\DataParameterInterface;
use webignition\BasilModels\Model\DataSet\DataSetInterface;

class DataSetValidator
{
    public const REASON_DATASET_INCOMPLETE = 'dataset-incomplete';
    public const CONTEXT_DATA_PARAMETER_NAME = 'data-parameter-name';

    public static function create(): DataSetValidator
    {
        return new DataSetValidator();
    }

    public function validate(DataSetInterface $dataSet, DataParameterInterface $dataParameter): ResultInterface
    {
        $property = $dataParameter->getProperty();

        if (false === $dataSet->hasParameterNames([$property])) {
            return (new InvalidResult($dataSet, ResultType::DATASET, self::REASON_DATASET_INCOMPLETE))
                ->withContext([
                    self::CONTEXT_DATA_PARAMETER_NAME => $property,
                ])
            ;
        }

        return new ValidResult($dataSet);
    }
}
