<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Validator\Step;

use SmartAssert\Compiler\Loader\Validator\Action\ActionValidator;
use SmartAssert\Compiler\Loader\Validator\Assertion\AssertionValidator;
use SmartAssert\Compiler\Loader\Validator\DataValidator;
use SmartAssert\Compiler\Loader\Validator\InvalidResult;
use SmartAssert\Compiler\Loader\Validator\InvalidResultInterface;
use SmartAssert\Compiler\Loader\Validator\ResultInterface;
use SmartAssert\Compiler\Loader\Validator\ResultType;
use SmartAssert\Compiler\Loader\Validator\ValidResult;
use webignition\BasilModels\Model\DataParameter\DataParameter;
use webignition\BasilModels\Model\DataParameter\DataParameterInterface;
use webignition\BasilModels\Model\DataSet\DataSetCollection;
use webignition\BasilModels\Model\Statement\Action\ActionInterface;
use webignition\BasilModels\Model\Statement\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Statement\StatementInterface;
use webignition\BasilModels\Model\Step\StepInterface;

class StepValidator
{
    public const REASON_NO_ASSERTIONS = 'step-no-assertions';
    public const REASON_INVALID_ACTION = 'step-invalid-action';
    public const REASON_INVALID_ASSERTION = 'step-invalid-assertion';
    public const REASON_DATA_SET_EMPTY = 'step-data-set-empty';
    public const REASON_DATA_INVALID = 'step-data-invalid';
    public const CONTEXT_STATEMENT = 'statement';

    private ActionValidator $actionValidator;
    private AssertionValidator $assertionValidator;
    private DataValidator $dataValidator;

    public function __construct(
        ActionValidator $actionValidator,
        AssertionValidator $assertionValidator,
        DataValidator $dataSetCollectionValidator
    ) {
        $this->actionValidator = $actionValidator;
        $this->assertionValidator = $assertionValidator;
        $this->dataValidator = $dataSetCollectionValidator;
    }

    public static function create(): StepValidator
    {
        return new StepValidator(
            ActionValidator::create(),
            AssertionValidator::create(),
            DataValidator::create()
        );
    }

    public function validate(StepInterface $step): ResultInterface
    {
        $assertions = $step->getAssertions();
        if (0 === count($assertions)) {
            return new InvalidResult(
                $step,
                ResultType::STEP,
                self::REASON_NO_ASSERTIONS
            );
        }

        $stepDataParameterNames = $step->getDataParameterNames();
        $stepData = $step->getData();
        if (count($stepDataParameterNames) > 0 && (null === $stepData || 0 === count($stepData))) {
            return new InvalidResult(
                $step,
                ResultType::STEP,
                self::REASON_DATA_SET_EMPTY
            );
        }

        foreach ($step->getActions() as $action) {
            if (!$action instanceof ActionInterface) {
                continue;
            }

            $actionValidationResult = $this->actionValidator->validate($action);

            if ($actionValidationResult instanceof InvalidResultInterface) {
                return new InvalidResult(
                    $step,
                    ResultType::STEP,
                    self::REASON_INVALID_ACTION,
                    $actionValidationResult
                );
            }

            if ($action->isInput()) {
                $value = (string) $action->getValue();

                if (DataParameter::is($value)) {
                    $dataValidationResult = $this->validateStatementData($step, $action, new DataParameter($value));

                    if ($dataValidationResult instanceof InvalidResultInterface) {
                        return $dataValidationResult;
                    }
                }
            }
        }

        foreach ($assertions as $assertion) {
            if (!$assertion instanceof AssertionInterface) {
                continue;
            }

            $assertionValidationResult = $this->assertionValidator->validate($assertion);

            if ($assertionValidationResult instanceof InvalidResultInterface) {
                return new InvalidResult(
                    $step,
                    ResultType::STEP,
                    self::REASON_INVALID_ASSERTION,
                    $assertionValidationResult
                );
            }

            $identifier = $assertion->getIdentifier();

            if (is_string($identifier) && DataParameter::is($identifier)) {
                $dataValidationResult = $this->validateStatementData($step, $assertion, new DataParameter($identifier));

                if ($dataValidationResult instanceof InvalidResultInterface) {
                    return $dataValidationResult;
                }
            }

            if ($assertion->isComparison()) {
                $value = (string) $assertion->getValue();

                if (DataParameter::is($value)) {
                    $dataValidationResult = $this->validateStatementData($step, $assertion, new DataParameter($value));

                    if ($dataValidationResult instanceof InvalidResultInterface) {
                        return $dataValidationResult;
                    }
                }
            }
        }

        $stepDataParameterNames = $step->getDataParameterNames();

        if (count($stepDataParameterNames) > 0) {
            $data = $step->getData();

            if (null === $data || 0 === count($data)) {
                return new InvalidResult(
                    $step,
                    ResultType::STEP,
                    self::REASON_DATA_SET_EMPTY
                );
            }
        }

        return new ValidResult($step);
    }

    private function validateStatementData(
        StepInterface $step,
        StatementInterface $statement,
        DataParameterInterface $dataParameter
    ): ?ResultInterface {
        $stepData = $step->getData() ?? new DataSetCollection([]);

        $dataValidationResult = $this->dataValidator->validate($stepData, $dataParameter);

        if ($dataValidationResult instanceof InvalidResultInterface) {
            $result = new InvalidResult(
                $step,
                ResultType::STEP,
                self::REASON_DATA_INVALID,
                $dataValidationResult
            );

            return $result->withContext([
                self::CONTEXT_STATEMENT => $statement,
            ]);
        }

        return null;
    }
}
