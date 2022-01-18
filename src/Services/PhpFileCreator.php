<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Services;

class PhpFileCreator
{
    private const TEMPLATE = <<< 'EOT'
<?php

namespace SmartAssert\Compiler\Generated;

%s

EOT;

    public function __construct(
        private string $outputDirectory
    ) {
    }

    public function create(string $className, string $code): string
    {
        $content = sprintf(self::TEMPLATE, $code);

        $filename = $className . '.php';
        $path = $this->outputDirectory . '/' . $filename;

        file_put_contents($path, $content);

        return $filename;
    }
}
