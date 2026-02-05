<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Exception;

use webignition\BasilModels\Parser\Exception\UnparseableDataExceptionInterface;
use webignition\BasilModels\Parser\Exception\UnparseableStepException;
use webignition\BasilModels\Parser\Exception\UnparseableTestException;

class ParseException extends \Exception
{
    private UnparseableDataExceptionInterface $unparseableDataException;

    public function __construct(
        private string $testPath,
        private string $subjectPath,
        UnparseableDataExceptionInterface $unparseableDataException
    ) {
        parent::__construct(
            sprintf('Parse error when loading: %s', $unparseableDataException->getMessage()),
            0,
            $unparseableDataException
        );
        $this->unparseableDataException = $unparseableDataException;
    }

    public function getTestPath(): string
    {
        return $this->testPath;
    }

    public function getSubjectPath(): string
    {
        return $this->subjectPath;
    }

    public function getUnparseableDataException(): UnparseableDataExceptionInterface
    {
        return $this->unparseableDataException;
    }

    public function isUnparseableTestException(): bool
    {
        return $this->unparseableDataException instanceof UnparseableTestException;
    }

    public function isUnparseableStepException(): bool
    {
        return $this->unparseableDataException instanceof UnparseableStepException;
    }
}
