<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Functional\Services;

use SmartAssert\Compiler\Model\CompiledTest;
use SmartAssert\Compiler\Services\TestWriter;

class TestWriterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider generateDataProvider
     */
    public function testWrite(
        CompiledTest $compiledTest,
        string $outputDirectory,
        string $expectedGeneratedCode
    ): void {
        $testWriter = TestWriter::createWriter($outputDirectory);

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
                'expectedGeneratedCode' => '<?php' . "\n" .
                    "\n" .
                    'namespace SmartAssert\Compiler\Generated;' . "\n" .
                    "\n" .
                    'compiled test code' . "\n",
            ],
        ];
    }
}
