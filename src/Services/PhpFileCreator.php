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

    /**
     * @param non-empty-string $outputDirectory
     */
    public function __construct(
        private readonly string $outputDirectory
    ) {
    }

    public function create(string $className, string $code): string
    {
        $content = sprintf(self::TEMPLATE, $code);

        $filename = $className . '.php';

        $path = $this->outputDirectory;
        if (!str_ends_with($path, '/')) {
            $path .= '/';
        }
        $path .= $filename;

        file_put_contents($path, $content);

        return $filename;
    }
}
