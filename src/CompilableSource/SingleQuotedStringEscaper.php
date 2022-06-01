<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource;

class SingleQuotedStringEscaper
{
    public static function create(): SingleQuotedStringEscaper
    {
        return new SingleQuotedStringEscaper();
    }

    public function escape(string $string): string
    {
        return addcslashes($string, "'\\");
    }
}
