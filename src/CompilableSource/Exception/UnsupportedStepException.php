<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Exception;

use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\BasilModels\Model\Step\StepInterface;

class UnsupportedStepException extends \Exception
{
    public const CODE_UNSUPPORTED_ACTION = 1;
    public const CODE_UNSUPPORTED_ASSERTION = 2;

    public function __construct(
        private StepInterface $step,
        private UnsupportedStatementException $unsupportedStatementException
    ) {
        $code = $unsupportedStatementException->getStatement() instanceof ActionInterface
            ? self::CODE_UNSUPPORTED_ACTION
            : self::CODE_UNSUPPORTED_ASSERTION;

        parent::__construct('Unsupported step', $code, $unsupportedStatementException);
    }

    public function getStep(): StepInterface
    {
        return $this->step;
    }

    public function getUnsupportedStatementException(): UnsupportedStatementException
    {
        return $this->unsupportedStatementException;
    }
}
