<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Validator;

use webignition\BasilModels\Model\DataParameter\DataParameterInterface;
use webignition\BasilModels\Model\DataSet\DataSetCollectionInterface;

class DataValidator
{
    public const REASON_DATASET_INVALID = 'data-dataset-invalid';
    public const REASON_DATA_EMPTY = 'data-empty';

    private DataSetValidator $dataSetValidator;

    public function __construct(DataSetValidator $dataSetValidator)
    {
        $this->dataSetValidator = $dataSetValidator;
    }

    public static function create(): DataValidator
    {
        return new DataValidator(
            DataSetValidator::create()
        );
    }

    public function validate(DataSetCollectionInterface $data, DataParameterInterface $dataParameter): ?ResultInterface
    {
        $localData = clone $data;
        $localData->rewind();

        if (0 === count($localData)) {
            return new InvalidResult(
                $data,
                ResultType::DATA,
                self::REASON_DATA_EMPTY
            );
        }

        foreach ($localData as $dataSet) {
            $dataSetValidationResult = $this->dataSetValidator->validate($dataSet, $dataParameter);

            if ($dataSetValidationResult instanceof InvalidResultInterface) {
                return new InvalidResult(
                    $data,
                    ResultType::DATA,
                    self::REASON_DATASET_INVALID,
                    $dataSetValidationResult
                );
            }
        }

        return new ValidResult($data);
    }
}
