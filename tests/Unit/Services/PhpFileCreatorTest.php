<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Services;

use phpmock\mockery\PHPMockery;
use SmartAssert\Compiler\Services\PhpFileCreator;
use SmartAssert\Compiler\Tests\Unit\AbstractBaseTest;

class PhpFileCreatorTest extends AbstractBaseTest
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(
        string $outputDirectory,
        string $className,
        string $code,
        string $expectedFilePutContentsFilename,
        string $expectedFilePutContentsData,
        string $expectedCreatedFilename
    ): void {
        PHPMockery::mock('SmartAssert\Compiler\Services', 'file_put_contents')
            ->with($expectedFilePutContentsFilename, $expectedFilePutContentsData)
            ->andReturn(strlen($expectedFilePutContentsData))
        ;

        $creator = new PhpFileCreator($outputDirectory);
        $createdFileName = $creator->create($className, $code);

        self::assertSame($expectedCreatedFilename, $createdFileName);
    }

    /**
     * @return array<mixed>
     */
    public function createDataProvider(): array
    {
        return [
            'no output directory' => [
                'outputDirectory' => '',
                'className' => 'TestClassName',
                'code' => 'echo "test code";',
                'expectedFilePutContentsFilename' => '/TestClassName.php',
                'expectedFilePutContentsData' => sprintf($this->getPhpFileCreatorTemplate(), 'echo "test code";'),
                'expectedCreatedFilename' => 'TestClassName.php',
            ],
            'has output directory' => [
                'outputDirectory' => '/build',
                'className' => 'TestClassName',
                'code' => 'echo "test code";',
                'expectedFilePutContentsFilename' => '/build/TestClassName.php',
                'expectedFilePutContentsData' => sprintf($this->getPhpFileCreatorTemplate(), 'echo "test code";'),
                'expectedCreatedFilename' => 'TestClassName.php',
            ],
        ];
    }

    private function getPhpFileCreatorTemplate(): string
    {
        return (new \ReflectionClass(PhpFileCreator::class))->getConstant('TEMPLATE');
    }
}
