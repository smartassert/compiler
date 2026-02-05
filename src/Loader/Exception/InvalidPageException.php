<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Exception;

use SmartAssert\Compiler\Loader\Validator\InvalidResultInterface;

class InvalidPageException extends \Exception
{
    private string $importName;
    private string $path;
    private InvalidResultInterface $validationResult;
    private string $testPath;

    public function __construct(string $importName, string $path, InvalidResultInterface $validationResult)
    {
        parent::__construct(sprintf(
            'Invalid page "%s" at path "%s": %s',
            $importName,
            $path,
            $validationResult->getReason()
        ));

        $this->importName = $importName;
        $this->path = $path;
        $this->validationResult = $validationResult;
    }

    public function getImportName(): string
    {
        return $this->importName;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getValidationResult(): InvalidResultInterface
    {
        return $this->validationResult;
    }

    public function getTestPath(): ?string
    {
        return $this->testPath;
    }

    public function setTestPath(string $testPath): void
    {
        $this->testPath = $testPath;
    }
}
