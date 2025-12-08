<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Services;

use SmartAssert\Compiler\ExitCode;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilerModels\Factory\ErrorOutputFactory as ErrorOutputModelFactory;
use webignition\BasilCompilerModels\Model\ErrorOutput;
use webignition\BasilCompilerModels\Model\ErrorOutputInterface;
use webignition\BasilLoader\Exception\EmptyTestException;
use webignition\BasilLoader\Exception\InvalidPageException;
use webignition\BasilLoader\Exception\InvalidTestException;
use webignition\BasilLoader\Exception\NonRetrievableImportException;
use webignition\BasilLoader\Exception\ParseException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilLoader\Resolver\CircularStepImportException;
use webignition\BasilLoader\Resolver\UnknownElementException;
use webignition\BasilLoader\Resolver\UnknownPageElementException;
use webignition\BasilModels\Parser\Exception\UnparseableActionException;
use webignition\BasilModels\Parser\Exception\UnparseableAssertionException;
use webignition\BasilModels\Parser\Exception\UnparseableDataExceptionInterface;
use webignition\BasilModels\Parser\Exception\UnparseableStatementException;
use webignition\BasilModels\Parser\Exception\UnparseableStepException;
use webignition\BasilModels\Parser\Exception\UnparseableTestException;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\Stubble\UnresolvedVariableException;

class ErrorOutputFactory
{
    /**
     * @var array{action: array<int, string>, assertion: array<int, string>}
     */
    private array $unparseableStatementErrorMessages = [
        'action' => [
            UnparseableActionException::CODE_EMPTY => 'empty',
            UnparseableActionException::CODE_EMPTY_INPUT_ACTION_VALUE => 'empty-value',
            UnparseableActionException::CODE_INVALID_IDENTIFIER => 'invalid-identifier',
        ],
        'assertion' => [
            UnparseableAssertionException::CODE_EMPTY => 'empty',
            UnparseableAssertionException::CODE_EMPTY_COMPARISON => 'empty-comparison',
            UnparseableAssertionException::CODE_EMPTY_IDENTIFIER => 'empty-identifier',
            UnparseableAssertionException::CODE_EMPTY_VALUE => 'empty-value',
        ],
    ];

    public function __construct(
        private readonly ValidatorInvalidResultSerializer $validatorInvalidResultSerializer
    ) {}

    public function createForException(\Exception $exception): ErrorOutputInterface
    {
        if ($exception instanceof YamlLoaderException) {
            $message = $exception->getPrevious() instanceof \Exception
                ? $exception->getPrevious()->getMessage()
                : $exception->getMessage();

            return new ErrorOutput($message, ExitCode::INVALID_YAML->value, [
                'path' => $exception->getPath(),
            ]);
        }

        if ($exception instanceof CircularStepImportException) {
            return new ErrorOutput($exception->getMessage(), ExitCode::CIRCULAR_STEP_IMPORT->value, [
                'import_name' => $exception->getImportName(),
            ]);
        }

        if ($exception instanceof EmptyTestException) {
            return new ErrorOutput($exception->getMessage(), ExitCode::EMPTY_TEST->value, [
                'path' => $exception->getPath(),
            ]);
        }

        if ($exception instanceof InvalidPageException) {
            return new ErrorOutput($exception->getMessage(), ExitCode::INVALID_PAGE->value, [
                'test_path' => $exception->getTestPath(),
                'import_name' => $exception->getImportName(),
                'page_path' => $exception->getPath(),
                'validation_result' => $this->validatorInvalidResultSerializer->serializeToArray(
                    $exception->getValidationResult()
                ),
            ]);
        }

        if ($exception instanceof InvalidTestException) {
            return new ErrorOutput($exception->getMessage(), ExitCode::INVALID_TEST->value, [
                'test_path' => $exception->getPath(),
                'validation_result' => $this->validatorInvalidResultSerializer->serializeToArray(
                    $exception->getValidationResult()
                ),
            ]);
        }

        if ($exception instanceof NonRetrievableImportException) {
            return $this->createForNonRetrievableImportException($exception);
        }

        if ($exception instanceof ParseException) {
            return $this->createForParseException($exception);
        }

        if ($exception instanceof UnknownElementException && !$exception instanceof UnknownPageElementException) {
            return new ErrorOutput($exception->getMessage(), ExitCode::UNKNOWN_ELEMENT->value, array_merge(
                [
                    'element_name' => $exception->getElementName(),
                ],
                $this->createErrorOutputContextFromExceptionContext($exception)
            ));
        }

        if ($exception instanceof UnknownItemException) {
            return new ErrorOutput($exception->getMessage(), ExitCode::UNKNOWN_ITEM->value, array_merge(
                [
                    'type' => $exception->getType(),
                    'name' => $exception->getName(),
                ],
                $this->createErrorOutputContextFromExceptionContext($exception)
            ));
        }

        if ($exception instanceof UnknownPageElementException) {
            return new ErrorOutput($exception->getMessage(), ExitCode::UNKNOWN_PAGE_ELEMENT->value, array_merge(
                [
                    'import_name' => $exception->getImportName(),
                    'element_name' => $exception->getElementName(),
                ],
                $this->createErrorOutputContextFromExceptionContext($exception)
            ));
        }

        if ($exception instanceof UnresolvedVariableException) {
            return new ErrorOutput($exception->getMessage(), ExitCode::UNRESOLVED_PLACEHOLDER->value, [
                'placeholder' => $exception->getVariable(),
                'content' => $exception->getTemplate(),
            ]);
        }

        if ($exception instanceof UnsupportedStepException) {
            return new ErrorOutput(
                $exception->getMessage(),
                ExitCode::UNSUPPORTED_STEP->value,
                $this->createErrorOutputContextFromUnsupportedStepException($exception)
            );
        }

        return new ErrorOutput('An unknown error has occurred', ErrorOutputModelFactory::CODE_UNKNOWN);
    }

    private function createForNonRetrievableImportException(
        NonRetrievableImportException $exception,
    ): ErrorOutputInterface {
        $yamlLoaderException = $exception->getYamlLoaderException();

        $loaderMessage = $yamlLoaderException->getMessage();
        $loaderPreviousException = $yamlLoaderException->getPrevious();

        if ($loaderPreviousException instanceof \Exception) {
            $loaderMessage = $loaderPreviousException->getMessage();
        }

        return new ErrorOutput($exception->getMessage(), ExitCode::NON_RETRIEVABLE_IMPORT->value, [
            'test_path' => $exception->getTestPath(),
            'type' => $exception->getType(),
            'name' => $exception->getName(),
            'import_path' => $exception->getPath(),
            'loader_error' => [
                'message' => $loaderMessage,
                'path' => $yamlLoaderException->getPath(),
            ],
        ]);
    }

    private function createForParseException(ParseException $parseException): ErrorOutputInterface
    {
        $unparseableDataException = $parseException->getUnparseableDataException();
        $unparseableStatementException = $this->findUnparseableStatementException($unparseableDataException);

        $context = [
            'type' => $unparseableDataException instanceof UnparseableTestException ? 'test' : 'step',
            'test_path' => $parseException->getTestPath(),
        ];

        if ($unparseableDataException instanceof UnparseableTestException) {
            $unparseableStepException = $unparseableDataException->getUnparseableStepException();

            $context['step_name'] = $unparseableStepException->getStepName();
            $context['reason'] = $this->createInvalidStepStatementsDataReason($unparseableStepException->getCode());
        }

        if ($unparseableDataException instanceof UnparseableStepException) {
            $context['step_path'] = $parseException->getSubjectPath();
            $context['reason'] = $this->createInvalidStepStatementsDataReason($unparseableDataException->getCode());
        }

        if (
            $unparseableStatementException instanceof UnparseableActionException
            || $unparseableStatementException instanceof UnparseableAssertionException
        ) {
            $statementType = $unparseableStatementException instanceof UnparseableActionException
                ? 'action'
                : 'assertion';

            $code = $unparseableStatementException->getCode();

            $context['statement_type'] = $statementType;
            $context['statement'] = $unparseableStatementException->getStatement();
            $context['reason']
                = $this->unparseableStatementErrorMessages[$statementType][$code] ?? 'unknown';
        }

        return new ErrorOutput($unparseableDataException->getMessage(), ExitCode::UNPARSEABLE_DATA->value, $context);
    }

    /**
     * @return array<string, string>
     */
    private function createErrorOutputContextFromUnsupportedStepException(
        UnsupportedStepException $exception,
    ): array {
        $statementType = UnsupportedStepException::CODE_UNSUPPORTED_ACTION === $exception->getCode()
            ? 'action'
            : 'assertion';

        $unsupportedStatementException = $exception->getUnsupportedStatementException();

        $context = [
            'statement_type' => $statementType,
            'statement' => (string) $unsupportedStatementException->getStatement(),
        ];

        $unsupportedContentException = $unsupportedStatementException->getUnsupportedContentException();
        if ($unsupportedContentException instanceof UnsupportedContentException) {
            $context = array_merge($context, [
                'content_type' => $unsupportedContentException->getType(),
                'content' => (string) $unsupportedContentException->getContent(),
            ]);
        }

        return $context;
    }

    /**
     * @return array{test_path: string, step_name: string, statement: string}
     */
    private function createErrorOutputContextFromExceptionContext(
        UnknownElementException|UnknownItemException $exception
    ): array {
        return [
            'test_path' => (string) $exception->getTestName(),
            'step_name' => (string) $exception->getStepName(),
            'statement' => (string) $exception->getContent(),
        ];
    }

    private function findUnparseableStatementException(
        UnparseableDataExceptionInterface $unparseableDataException
    ): ?UnparseableStatementException {
        $unparseableStatementException = null;

        if ($unparseableDataException instanceof UnparseableStepException) {
            $unparseableStatementException = $unparseableDataException->getUnparseableStatementException();
        } elseif ($unparseableDataException instanceof UnparseableTestException) {
            $unparseableStepException = $unparseableDataException->getUnparseableStepException();
            $unparseableStatementException = $unparseableStepException->getUnparseableStatementException();
        }

        return $unparseableStatementException;
    }

    private function createInvalidStepStatementsDataReason(int $code): string
    {
        if (UnparseableStepException::CODE_INVALID_ACTIONS_DATA === $code) {
            return 'invalid-actions-data';
        }

        if (UnparseableStepException::CODE_INVALID_ASSERTIONS_DATA === $code) {
            return 'invalid-assertions-data';
        }

        return 'unknown';
    }
}
