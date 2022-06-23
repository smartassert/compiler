<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Exception\EmptyOutputDirectoryPathException;
use SmartAssert\Compiler\Services\CommandFactory;
use Symfony\Component\Console\Output\OutputInterface;

class CommandFactoryTest extends TestCase
{
    public function testCreateGenerateCommandThrowsEmptyOutputDirectoryPathException(): void
    {
        self::expectException(EmptyOutputDirectoryPathException::class);

        CommandFactory::createGenerateCommand(
            \Mockery::mock(OutputInterface::class),
            \Mockery::mock(OutputInterface::class),
            []
        );
    }
}
