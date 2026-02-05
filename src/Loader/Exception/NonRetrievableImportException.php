<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Exception;

class NonRetrievableImportException extends \Exception
{
    public const TYPE_DATA_PROVIDER = 'data-provider';
    public const TYPE_PAGE = 'page';
    public const TYPE_STEP = 'step';

    private string $type;
    private string $name;
    private string $path;
    private YamlLoaderException $yamlLoaderException;
    private string $testPath;

    public function __construct(
        string $type,
        string $name,
        string $path,
        YamlLoaderException $previous
    ) {
        parent::__construct(
            sprintf('Cannot retrieve %s "%s" from "%s"', $type, $name, $path),
            0,
            $previous
        );

        $this->type = $type;
        $this->name = $name;
        $this->path = $path;
        $this->yamlLoaderException = $previous;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getYamlLoaderException(): YamlLoaderException
    {
        return $this->yamlLoaderException;
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
