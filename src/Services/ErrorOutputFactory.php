<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Services;

use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilerModels\Configuration;
use webignition\BasilCompilerModels\ErrorOutput;
use webignition\BasilCompilerModels\ErrorOutputInterface;
use webignition\BasilContextAwareException\ExceptionContext\ExceptionContextInterface;
use webignition\BasilLoader\Exception\EmptyTestException;
use webignition\BasilLoader\Exception\InvalidPageException;
use webignition\BasilLoader\Exception\InvalidTestException;
use webignition\BasilLoader\Exception\NonRetrievableImportException;
use webignition\BasilLoader\Exception\ParseException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilLoader\Resolver\CircularStepImportException;
use webignition\BasilLoader\Resolver\UnknownElementException;
use webignition\BasilLoader\Resolver\UnknownPageElementException;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\BasilParser\Exception\UnparseableActionException;
use webignition\BasilParser\Exception\UnparseableAssertionException;
use webignition\BasilParser\Exception\UnparseableDataExceptionInterface;
use webignition\BasilParser\Exception\UnparseableStatementException;
use webignition\BasilParser\Exception\UnparseableStepException;
use webignition\BasilParser\Exception\UnparseableTestException;
use webignition\Stubble\UnresolvedVariableException;

class ErrorOutputFactory
{
    public const UNPARSEABLE_ACTION_EMPTY = 'empty';
    public const UNPARSEABLE_ACTION_EMPTY_VALUE = 'empty-value';
    public const UNPARSEABLE_ACTION_INVALID_IDENTIFIER = 'invalid-identifier';
    public const UNPARSEABLE_ASSERTION_EMPTY = 'empty';
    public const UNPARSEABLE_ASSERTION_EMPTY_COMPARISON = 'empty-comparison';
    public const UNPARSEABLE_ASSERTION_EMPTY_IDENTIFIER = 'empty-identifier';
    public const UNPARSEABLE_ASSERTION_EMPTY_VALUE = 'empty-value';
    public const UNPARSEABLE_STEP_INVALID_ACTIONS_DATA = 'invalid-actions-data';
    public const UNPARSEABLE_STEP_INVALID_ASSERTIONS_DATA = 'invalid-assertions-data';
    public const REASON_UNKNOWN = 'unknown';

    public const CODE_COMMAND_CONFIG_SOURCE_EMPTY = 100;
    public const CODE_COMMAND_CONFIG_SOURCE_DOES_NOT_EXIST = 101;
    public const CODE_COMMAND_CONFIG_SOURCE_NOT_READABLE = 102;
    public const CODE_COMMAND_CONFIG_TARGET_EMPTY = 103;
    public const CODE_COMMAND_CONFIG_TARGET_DOES_NOT_EXIST = 104;
    public const CODE_COMMAND_CONFIG_TARGET_NOT_A_DIRECTORY = 105;
    public const CODE_COMMAND_CONFIG_TARGET_NOT_WRITABLE = 106;
    public const CODE_COMMAND_CONFIG_SOURCE_NOT_ABSOLUTE = 107;
    public const CODE_COMMAND_CONFIG_TARGET_NOT_ABSOLUTE = 108;
    public const CODE_LOADER_INVALID_YAML = 200;
    public const CODE_LOADER_CIRCULAR_STEP_IMPORT = 201;
    public const CODE_LOADER_EMPTY_TEST = 202;
    public const CODE_LOADER_INVALID_PAGE = 203;
    public const CODE_LOADER_INVALID_TEST = 204;
    public const CODE_LOADER_NON_RETRIEVABLE_IMPORT = 205;
    public const CODE_LOADER_UNPARSEABLE_DATA = 206;
    public const CODE_LOADER_UNKNOWN_ELEMENT = 207;
    public const CODE_LOADER_UNKNOWN_ITEM = 208;
    public const CODE_LOADER_UNKNOWN_PAGE_ELEMENT = 209;
    public const CODE_GENERATOR_UNRESOLVED_PLACEHOLDER = 211;
    public const CODE_GENERATOR_UNSUPPORTED_STEP = 212;

    public const MESSAGE_COMMAND_CONFIG_SOURCE_EMPTY = 'source empty; call with --source=SOURCE';
    public const MESSAGE_COMMAND_CONFIG_SOURCE_DOES_NOT_EXIST = 'source invalid; does not exist';
    public const MESSAGE_COMMAND_CONFIG_SOURCE_NOT_READABLE = 'source invalid; file is not readable';
    public const MESSAGE_COMMAND_CONFIG_TARGET_EMPTY = 'target empty; call with --target=TARGET';
    public const MESSAGE_COMMAND_CONFIG_TARGET_DOES_NOT_EXIST = 'target invalid; does not exist';
    public const MESSAGE_COMMAND_CONFIG_TARGET_NOT_A_DIRECTORY = 'target invalid; is not a directory (is it a file?)';
    public const MESSAGE_COMMAND_CONFIG_TARGET_NOT_WRITABLE = 'target invalid; directory is not writable';
    public const MESSAGE_COMMAND_CONFIG_SOURCE_NOT_ABSOLUTE = 'source invalid: path must be absolute';
    public const MESSAGE_COMMAND_CONFIG_TARGET_NOT_ABSOLUTE = 'target invalid: path must be absolute';

    /**
     * @var array{action: array<int, string>, assertion: array<int, string>}
     */
    private array $unparseableStatementErrorMessages = [
        'action' => [
            UnparseableActionException::CODE_EMPTY => self::UNPARSEABLE_ACTION_EMPTY,
            UnparseableActionException::CODE_EMPTY_INPUT_ACTION_VALUE => self::UNPARSEABLE_ACTION_EMPTY_VALUE,
            UnparseableActionException::CODE_INVALID_IDENTIFIER => self::UNPARSEABLE_ACTION_INVALID_IDENTIFIER,
        ],
        'assertion' => [
            UnparseableAssertionException::CODE_EMPTY => self::UNPARSEABLE_ASSERTION_EMPTY,
            UnparseableAssertionException::CODE_EMPTY_COMPARISON => self::UNPARSEABLE_ASSERTION_EMPTY_COMPARISON,
            UnparseableAssertionException::CODE_EMPTY_IDENTIFIER => self::UNPARSEABLE_ASSERTION_EMPTY_IDENTIFIER,
            UnparseableAssertionException::CODE_EMPTY_VALUE => self::UNPARSEABLE_ASSERTION_EMPTY_VALUE,
        ],
    ];

    /**
     * @var array<int, string>
     */
    private array $configurationErrorMessages = [
        self::CODE_COMMAND_CONFIG_SOURCE_EMPTY => self::MESSAGE_COMMAND_CONFIG_SOURCE_EMPTY,
        self::CODE_COMMAND_CONFIG_SOURCE_DOES_NOT_EXIST => self::MESSAGE_COMMAND_CONFIG_SOURCE_DOES_NOT_EXIST,
        self::CODE_COMMAND_CONFIG_SOURCE_NOT_READABLE => self::MESSAGE_COMMAND_CONFIG_SOURCE_NOT_READABLE,
        self::CODE_COMMAND_CONFIG_TARGET_EMPTY => self::MESSAGE_COMMAND_CONFIG_TARGET_EMPTY,
        self::CODE_COMMAND_CONFIG_TARGET_DOES_NOT_EXIST => self::MESSAGE_COMMAND_CONFIG_TARGET_DOES_NOT_EXIST,
        self::CODE_COMMAND_CONFIG_TARGET_NOT_A_DIRECTORY => self::MESSAGE_COMMAND_CONFIG_TARGET_NOT_A_DIRECTORY,
        self::CODE_COMMAND_CONFIG_TARGET_NOT_WRITABLE => self::MESSAGE_COMMAND_CONFIG_TARGET_NOT_WRITABLE,
        self::CODE_COMMAND_CONFIG_SOURCE_NOT_ABSOLUTE => self::MESSAGE_COMMAND_CONFIG_SOURCE_NOT_ABSOLUTE,
        self::CODE_COMMAND_CONFIG_TARGET_NOT_ABSOLUTE => self::MESSAGE_COMMAND_CONFIG_TARGET_NOT_ABSOLUTE
    ];

    public function __construct(
        private ValidatorInvalidResultSerializer $validatorInvalidResultSerializer
    ) {
    }

    public function createFromInvalidConfiguration(int $configurationValidationState): ErrorOutputInterface
    {
        $errorCode = self::CODE_COMMAND_CONFIG_SOURCE_NOT_READABLE;

        if (Configuration::VALIDATION_STATE_TARGET_NOT_DIRECTORY === $configurationValidationState) {
            $errorCode = self::CODE_COMMAND_CONFIG_TARGET_NOT_A_DIRECTORY;
        }

        if (Configuration::VALIDATION_STATE_TARGET_NOT_WRITABLE === $configurationValidationState) {
            $errorCode = self::CODE_COMMAND_CONFIG_TARGET_NOT_WRITABLE;
        }

        if (Configuration::VALIDATION_STATE_SOURCE_NOT_ABSOLUTE === $configurationValidationState) {
            $errorCode = self::CODE_COMMAND_CONFIG_SOURCE_NOT_ABSOLUTE;
        }

        if (Configuration::VALIDATION_STATE_TARGET_NOT_ABSOLUTE === $configurationValidationState) {
            $errorCode = self::CODE_COMMAND_CONFIG_TARGET_NOT_ABSOLUTE;
        }

        if (Configuration::VALIDATION_STATE_SOURCE_EMPTY === $configurationValidationState) {
            $errorCode = self::CODE_COMMAND_CONFIG_SOURCE_EMPTY;
        }

        if (Configuration::VALIDATION_STATE_TARGET_EMPTY === $configurationValidationState) {
            $errorCode = self::CODE_COMMAND_CONFIG_TARGET_EMPTY;
        }

        return $this->createForConfigurationErrorCode($errorCode);
    }

    public function createForException(\Exception $exception): ErrorOutputInterface
    {
        if ($exception instanceof YamlLoaderException) {
            return $this->createForYamlLoaderException($exception);
        }

        if ($exception instanceof CircularStepImportException) {
            return $this->createForCircularStepImportException($exception);
        }

        if ($exception instanceof EmptyTestException) {
            return $this->createForEmptyTestException($exception);
        }

        if ($exception instanceof InvalidPageException) {
            return $this->createForInvalidPageException($exception);
        }

        if ($exception instanceof InvalidTestException) {
            return $this->createForInvalidTestException($exception);
        }

        if ($exception instanceof NonRetrievableImportException) {
            return $this->createForNonRetrievableImportException($exception);
        }

        if ($exception instanceof ParseException) {
            return $this->createForParseException($exception);
        }

        if ($exception instanceof UnknownElementException && !$exception instanceof UnknownPageElementException) {
            return $this->createForUnknownElementException($exception);
        }

        if ($exception instanceof UnknownItemException) {
            return $this->createForUnknownItemException($exception);
        }

        if ($exception instanceof UnknownPageElementException) {
            return $this->createForUnknownPageElementException($exception);
        }

        if ($exception instanceof UnresolvedVariableException) {
            return $this->createForUnresolvedVariableException($exception);
        }

        if ($exception instanceof UnsupportedStepException) {
            return $this->createForUnsupportedStepException($exception);
        }

        return $this->createUnknownErrorOutput();
    }

    public function createForYamlLoaderException(YamlLoaderException $exception): ErrorOutputInterface
    {
        $message = $exception->getMessage();
        $previousException = $exception->getPrevious();

        if ($previousException instanceof \Exception) {
            $message = $previousException->getMessage();
        }

        return new ErrorOutput(
            $message,
            self::CODE_LOADER_INVALID_YAML,
            [
                'path' => $exception->getPath()
            ]
        );
    }

    public function createForCircularStepImportException(CircularStepImportException $exception): ErrorOutputInterface
    {
        return new ErrorOutput(
            $exception->getMessage(),
            self::CODE_LOADER_CIRCULAR_STEP_IMPORT,
            [
                'import_name' => $exception->getImportName(),
            ]
        );
    }

    public function createForEmptyTestException(EmptyTestException $exception): ErrorOutputInterface
    {
        return new ErrorOutput(
            $exception->getMessage(),
            self::CODE_LOADER_EMPTY_TEST,
            [
                'path' => $exception->getPath(),
            ]
        );
    }

    public function createForInvalidPageException(InvalidPageException $exception): ErrorOutputInterface
    {
        return new ErrorOutput(
            $exception->getMessage(),
            self::CODE_LOADER_INVALID_PAGE,
            [
                'test_path' => $exception->getTestPath(),
                'import_name' => $exception->getImportName(),
                'page_path' => $exception->getPath(),
                'validation_result' => $this->validatorInvalidResultSerializer->serializeToArray(
                    $exception->getValidationResult()
                )
            ]
        );
    }

    public function createForInvalidTestException(InvalidTestException $exception): ErrorOutputInterface
    {
        return new ErrorOutput(
            $exception->getMessage(),
            self::CODE_LOADER_INVALID_TEST,
            [
                'test_path' => $exception->getPath(),
                'validation_result' => $this->validatorInvalidResultSerializer->serializeToArray(
                    $exception->getValidationResult()
                )
            ]
        );
    }

    public function createForNonRetrievableImportException(
        NonRetrievableImportException $exception,
    ): ErrorOutputInterface {
        $yamlLoaderException = $exception->getYamlLoaderException();

        $loaderMessage = $yamlLoaderException->getMessage();
        $loaderPreviousException = $yamlLoaderException->getPrevious();

        if ($loaderPreviousException instanceof \Exception) {
            $loaderMessage = $loaderPreviousException->getMessage();
        }

        return new ErrorOutput(
            $exception->getMessage(),
            self::CODE_LOADER_NON_RETRIEVABLE_IMPORT,
            [
                'test_path' => $exception->getTestPath(),
                'type' => $exception->getType(),
                'name' => $exception->getName(),
                'import_path' => $exception->getPath(),
                'loader_error' => [
                    'message' => $loaderMessage,
                    'path' => $yamlLoaderException->getPath(),
                ]
            ]
        );
    }

    public function createForParseException(ParseException $parseException): ErrorOutputInterface
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
            $context['reason'] =
                $this->unparseableStatementErrorMessages[$statementType][$code] ?? self::REASON_UNKNOWN;
        }

        return new ErrorOutput($unparseableDataException->getMessage(), self::CODE_LOADER_UNPARSEABLE_DATA, $context);
    }

    public function createForUnknownElementException(UnknownElementException $exception): ErrorOutputInterface
    {
        return new ErrorOutput(
            $exception->getMessage(),
            self::CODE_LOADER_UNKNOWN_ELEMENT,
            array_merge(
                [
                    'element_name' => $exception->getElementName(),
                ],
                $this->createErrorOutputContextFromExceptionContext($exception->getExceptionContext())
            )
        );
    }

    public function createForUnknownItemException(UnknownItemException $exception): ErrorOutputInterface
    {
        return new ErrorOutput(
            $exception->getMessage(),
            self::CODE_LOADER_UNKNOWN_ITEM,
            array_merge(
                [
                    'type' => $exception->getType(),
                    'name' => $exception->getName(),
                ],
                $this->createErrorOutputContextFromExceptionContext($exception->getExceptionContext())
            )
        );
    }

    public function createForUnknownPageElementException(UnknownPageElementException $exception): ErrorOutputInterface
    {
        return new ErrorOutput(
            $exception->getMessage(),
            self::CODE_LOADER_UNKNOWN_PAGE_ELEMENT,
            array_merge(
                [
                    'import_name' => $exception->getImportName(),
                    'element_name' => $exception->getElementName(),
                ],
                $this->createErrorOutputContextFromExceptionContext($exception->getExceptionContext())
            )
        );
    }

    public function createForUnresolvedVariableException(UnresolvedVariableException $exception): ErrorOutputInterface
    {
        return new ErrorOutput(
            $exception->getMessage(),
            self::CODE_GENERATOR_UNRESOLVED_PLACEHOLDER,
            [
                'placeholder' => $exception->getVariable(),
                'content' => $exception->getTemplate(),
            ]
        );
    }

    public function createForUnsupportedStepException(UnsupportedStepException $exception): ErrorOutputInterface
    {
        return new ErrorOutput(
            $exception->getMessage(),
            self::CODE_GENERATOR_UNSUPPORTED_STEP,
            $this->createErrorOutputContextFromUnsupportedStepException($exception)
        );
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
     * @return array<string, string>
     */
    private function createErrorOutputContextFromExceptionContext(ExceptionContextInterface $exceptionContext): array
    {
        return [
            'test_path' => (string) $exceptionContext->getTestName(),
            'step_name' => (string) $exceptionContext->getStepName(),
            'statement' => (string) $exceptionContext->getContent(),
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

    private function createForConfigurationErrorCode(int $errorCode): ErrorOutputInterface
    {
        $errorMessage = $this->configurationErrorMessages[$errorCode] ?? self::REASON_UNKNOWN;

        return new ErrorOutput($errorMessage, $errorCode);
    }

    private function createUnknownErrorOutput(): ErrorOutputInterface
    {
        return new ErrorOutput(
            'An unknown error has occurred',
            ErrorOutput::CODE_UNKNOWN
        );
    }

    private function createInvalidStepStatementsDataReason(int $code): string
    {
        if (UnparseableStepException::CODE_INVALID_ACTIONS_DATA === $code) {
            return self::UNPARSEABLE_STEP_INVALID_ACTIONS_DATA;
        }

        if (UnparseableStepException::CODE_INVALID_ASSERTIONS_DATA === $code) {
            return self::UNPARSEABLE_STEP_INVALID_ASSERTIONS_DATA;
        }

        return self::REASON_UNKNOWN;
    }
}
