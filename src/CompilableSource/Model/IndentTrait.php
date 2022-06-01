<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model;

trait IndentTrait
{
    private function indent(string $content): string
    {
        $lines = explode("\n", $content);

        array_walk($lines, function (&$line) {
            if ('' !== $line) {
                $line = '    ' . $line;
            }
        });

        return implode("\n", $lines);
    }
}
