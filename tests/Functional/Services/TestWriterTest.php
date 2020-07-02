<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\Functional\Services;

use webignition\BasilCliCompiler\Model\CompiledTest;
use webignition\BasilCliCompiler\Services\TestWriter;
use webignition\BasilCliCompiler\Tests\Services\ProjectRootPathProvider;

class TestWriterTest extends \PHPUnit\Framework\TestCase
{
    private TestWriter $testWriter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testWriter = TestWriter::createWriter();
    }

    /**
     * @dataProvider generateDataProvider
     */
    public function testWrite(
        CompiledTest $compiledTest,
        string $outputDirectory,
        string $expectedGeneratedCode
    ) {
        $generatedTestOutput = $this->testWriter->write($compiledTest, $outputDirectory);
        $expectedCodePath = $outputDirectory . '/' . $generatedTestOutput->getTarget();

        self::assertFileExists($expectedCodePath);
        self::assertFileIsReadable($expectedCodePath);

        self::assertEquals($expectedGeneratedCode, file_get_contents($expectedCodePath));

        if (file_exists($expectedCodePath)) {
            unlink($expectedCodePath);
        }
    }

    public function generateDataProvider(): array
    {
        $root = (new ProjectRootPathProvider())->get();

        return [
            'default' => [
                'compiledTest' => new CompiledTest(
                    'compiled test code',
                    'ClassName',
                    'test.yml'
                ),
                'outputDirectory' => $root . '/tests/build/target',
                'expectedGeneratedCode' =>
                    '<?php' . "\n" .
                    "\n" .
                    'namespace webignition\BasilCliCompiler\Generated;' . "\n" .
                    "\n" .
                    'compiled test code' . "\n",
            ],
        ];
    }
}