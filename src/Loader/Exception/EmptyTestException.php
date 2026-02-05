<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Loader\Exception;

class EmptyTestException extends \Exception
{
    private string $path;

    public function __construct(string $path)
    {
        parent::__construct(sprintf('Empty test at path "%s"', $path));

        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
