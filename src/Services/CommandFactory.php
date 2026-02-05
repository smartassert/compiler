<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Services;

use SmartAssert\Compiler\Command\GenerateCommand;
use SmartAssert\Compiler\Exception\EmptyOutputDirectoryPathException;
use SmartAssert\Compiler\Loader\TestLoader;
use SmartAssert\Compiler\Model\Options;
use Symfony\Component\Console\Output\OutputInterface;

class CommandFactory
{
    private const TARGET_ARG_START_PATTERN = '/^--' . Options::OPTION_TARGET . '=/';

    /**
     * @param array<int, string> $cliArguments
     *
     * @throws EmptyOutputDirectoryPathException
     */
    public static function createGenerateCommand(
        OutputInterface $stdout,
        OutputInterface $stderr,
        array $cliArguments
    ): GenerateCommand {
        $outputDirectory = '';
        foreach ($cliArguments as $cliArgument) {
            if ('' === $outputDirectory && preg_match(self::TARGET_ARG_START_PATTERN, $cliArgument)) {
                $outputDirectory = (string) preg_replace(self::TARGET_ARG_START_PATTERN, '', $cliArgument);
            }
        }

        if ('' === $outputDirectory) {
            throw new EmptyOutputDirectoryPathException();
        }

        return new GenerateCommand(
            TestLoader::createLoader(),
            Compiler::createCompiler(),
            new TestWriter(new PhpFileCreator($outputDirectory)),
            new ErrorOutputFactory(new ValidatorInvalidResultSerializer()),
            new OutputRenderer($stdout, $stderr)
        );
    }
}
