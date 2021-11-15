<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\Services;

class ClassNameReplacer
{
    /**
     * @param string[] $classNames
     */
    public function replaceNamesInContent(string $output, array $classNames): string
    {
        foreach ($classNames as $className) {
            $output = $this->replaceNameInContent($output, $className);
        }

        return $output;
    }

    private function replaceNameInContent(string $output, string $className): string
    {
        return (string) preg_replace(
            '/Generated[a-zA-Z0-9]{32}Test/',
            $className,
            $output,
            1
        );
    }
}
