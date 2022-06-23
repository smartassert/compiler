<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Services;

use SmartAssert\Compiler\Model\CompiledTest;

class TestWriter
{
    public function __construct(
        private PhpFileCreator $phpFileCreator
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function write(CompiledTest $compiledTest, string $outputDirectory): string
    {
        $filename = $this->phpFileCreator->create($compiledTest->getClassName(), $compiledTest->getCode());

        return $outputDirectory . '/' . $filename;
    }
}
