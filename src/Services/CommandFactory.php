<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Services;

use SmartAssert\Compiler\Command\GenerateCommand;
use SmartAssert\Compiler\Model\Options;
use Symfony\Component\Console\Output\OutputInterface;
use webignition\BasilLoader\TestLoader;

class CommandFactory
{
    private const TARGET_ARG_START_PATTERN = '/^--' . Options::OPTION_TARGET . '=/';

    /**
     * @param array<int, string> $cliArguments
     */
    public static function createGenerateCommand(
        OutputInterface $stdout,
        OutputInterface $stderr,
        array $cliArguments
    ): GenerateCommand {
        return new GenerateCommand(
            TestLoader::createLoader(),
            Compiler::createCompiler(),
            TestWriter::createWriter(self::getOutputDirectory($cliArguments)),
            new ErrorOutputFactory(new ValidatorInvalidResultSerializer()),
            new OutputRenderer($stdout, $stderr),
            new ConfigurationFactory()
        );
    }

    /**
     * @param array<int, string> $cliArguments
     */
    private static function getOutputDirectory(array $cliArguments): string
    {
        foreach ($cliArguments as $cliArgument) {
            if (preg_match(self::TARGET_ARG_START_PATTERN, $cliArgument)) {
                return (string) preg_replace(self::TARGET_ARG_START_PATTERN, '', $cliArgument);
            }
        }

        return '';
    }
}
