<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Functional\Services;

use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Model\CompiledTest;
use SmartAssert\Compiler\Services\PhpFileCreator;
use SmartAssert\Compiler\Services\TestWriter;

class TestWriterTest extends TestCase
{
    /**
     * @param non-empty-string $outputDirectory
     *
     * @dataProvider generateDataProvider
     */
    public function testWrite(
        CompiledTest $compiledTest,
        string $outputDirectory,
        string $expectedGeneratedCode
    ): void {
        $testWriter = new TestWriter(new PhpFileCreator($outputDirectory));

        $target = $testWriter->write($compiledTest, $outputDirectory);

        self::assertFileExists($target);
        self::assertFileIsReadable($target);

        self::assertEquals($expectedGeneratedCode, file_get_contents($target));

        if (file_exists($target)) {
            unlink($target);
        }
    }

    /**
     * @return array<mixed>
     */
    public function generateDataProvider(): array
    {
        $root = getcwd();

        return [
            'default' => [
                'compiledTest' => new CompiledTest(
                    'compiled test code',
                    'ClassName'
                ),
                'outputDirectory' => $root . '/tests/build/target',
                'expectedGeneratedCode' => '<?php' . "\n"
                    . "\n"
                    . 'namespace SmartAssert\Compiler\Generated;' . "\n"
                    . "\n"
                    . 'compiled test code' . "\n",
            ],
        ];
    }
}
