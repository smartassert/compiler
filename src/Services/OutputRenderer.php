<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Services;

use Symfony\Component\Console\Output\OutputInterface as ConsoleOutputInterface;
use Symfony\Component\Yaml\Yaml;
use webignition\BasilCompilerModels\ErrorOutputInterface;
use webignition\BasilCompilerModels\OutputInterface;

class OutputRenderer
{
    private const YAML_DUMP_INLINE_DEPTH = 4;

    public function __construct(
        private ConsoleOutputInterface $stdout,
        private ConsoleOutputInterface $stderr
    ) {
    }

    public function render(OutputInterface $commandOutput): int
    {
        $output = $this->stdout;
        $exitCode = 0;

        if ($commandOutput instanceof ErrorOutputInterface) {
            $output = $this->stderr;
            $exitCode = $commandOutput->getCode();
        }

        $output->writeln(Yaml::dump(
            $commandOutput->getData(),
            self::YAML_DUMP_INLINE_DEPTH
        ));

        return $exitCode;
    }
}
