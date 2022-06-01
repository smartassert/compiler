<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Exception;

use webignition\BasilModels\Model\StatementInterface;

class UnsupportedStatementException extends \Exception
{
    public const CODE_UNKNOWN = 1;
    public const CODE_UNSUPPORTED_IDENTIFIER = 2;
    public const CODE_UNSUPPORTED_VALUE = 3;

    public function __construct(
        private StatementInterface $statement,
        private ?UnsupportedContentException $unsupportedContentException = null
    ) {
        $code = self::CODE_UNKNOWN;

        if ($unsupportedContentException instanceof UnsupportedContentException) {
            $code = UnsupportedContentException::TYPE_IDENTIFIER === $unsupportedContentException->getType()
                ? self::CODE_UNSUPPORTED_IDENTIFIER
                : self::CODE_UNSUPPORTED_VALUE;
        }

        parent::__construct(sprintf('Unsupported statement "%s"', $statement->getSource()), $code);
    }

    public function getStatement(): StatementInterface
    {
        return $this->statement;
    }

    public function getUnsupportedContentException(): ?UnsupportedContentException
    {
        return $this->unsupportedContentException;
    }
}
