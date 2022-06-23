<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Services;

use Symfony\Component\Console\Output\OutputInterface as ConsoleOutputInterface;
use Symfony\Component\Yaml\Yaml;
use webignition\BasilCompilerModels\Model\ErrorOutputInterface;
use webignition\BasilCompilerModels\Model\OutputInterface;

class OutputRenderer
{
    private const YAML_DUMP_INLINE_DEPTH = 4;

    public function __construct(
        private readonly ConsoleOutputInterface $stdout,
        private readonly ConsoleOutputInterface $stderr
    ) {
    }

    public function render(OutputInterface $output): int
    {
        $this->renderAsYamlDocument(
            $output->toArray(),
            $output instanceof ErrorOutputInterface ? $this->stderr : $this->stdout
        );

        return $output instanceof ErrorOutputInterface ? $output->getCode() : 0;
    }

    /**
     * @param array<mixed> $data
     */
    private function renderAsYamlDocument(array $data, ConsoleOutputInterface $output): void
    {
        $output->writeln(sprintf(
            <<< 'EOF'
            ---
            %s
            ...
            EOF,
            trim(Yaml::dump(
                $data,
                self::YAML_DUMP_INLINE_DEPTH
            ))
        ));
    }
}
