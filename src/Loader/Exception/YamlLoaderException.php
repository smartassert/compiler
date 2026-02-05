<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Exception;

use Symfony\Component\Yaml\Exception\ParseException;

class YamlLoaderException extends \Exception
{
    public const MESSAGE_DATA_IS_NOT_AN_ARRAY = 'Data is not an array';
    public const CODE_DATA_IS_NOT_AN_ARRAY = 100;

    public function __construct(string $message, int $code, private string $path, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function fromYamlParseException(ParseException $parseException, string $path): YamlLoaderException
    {
        return new YamlLoaderException(
            $parseException->getMessage(),
            $parseException->getCode(),
            $path,
            $parseException
        );
    }

    public static function createDataIsNotAnArrayException(string $path): YamlLoaderException
    {
        return new YamlLoaderException(
            self::MESSAGE_DATA_IS_NOT_AN_ARRAY,
            self::CODE_DATA_IS_NOT_AN_ARRAY,
            $path
        );
    }

    public function isFileDoesNotExistException(): bool
    {
        return preg_match('/does not exist\.$/', $this->getMessage()) > 0;
    }

    public function isFileCannotBeReadException(): bool
    {
        return preg_match('/cannot be read\.$/', $this->getMessage()) > 0;
    }

    public function isInvalidUtf8Exception(): bool
    {
        return preg_match('/does not appear to be valid UTF-8\.$/', $this->getMessage()) > 0;
    }

    public function isUnableToParseException(): bool
    {
        return 'Unable to parse.' === $this->getMessage();
    }

    public function isDataIsNotAnArrayException(): bool
    {
        return self::CODE_DATA_IS_NOT_AN_ARRAY === $this->getCode();
    }

    public function isInvalidYamlException(): bool
    {
        return !(
            $this->isFileDoesNotExistException()
            || $this->isFileCannotBeReadException()
            || $this->isInvalidUtf8Exception()
            || $this->isUnableToParseException()
            || $this->isDataIsNotAnArrayException()
        );
    }

    public function getPath(): ?string
    {
        return $this->path;
    }
}
