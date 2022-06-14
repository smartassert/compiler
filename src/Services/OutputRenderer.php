<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Services;

use Symfony\Component\Console\Output\OutputInterface as ConsoleOutputInterface;
use Symfony\Component\Yaml\Yaml;
use webignition\BasilCompilerModels\ErrorOutputInterface;
use webignition\BasilCompilerModels\TestManifest;

class OutputRenderer
{
    private const YAML_DUMP_INLINE_DEPTH = 4;

    public function __construct(
        private ConsoleOutputInterface $stdout,
        private ConsoleOutputInterface $stderr
    ) {
    }

    public function renderErrorOutput(ErrorOutputInterface $output): void
    {
        $this->renderAsYamlDocument($output->toArray(), $this->stderr);
    }

    /**
     * @param TestManifest[] $testManifests
     */
    public function renderTestManifests(array $testManifests): void
    {
        $output = $this->stdout;
        $exitCode = 0;

        foreach ($testManifests as $testManifest) {
            $this->renderAsYamlDocument($testManifest->toArray(), $output);
        }
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
