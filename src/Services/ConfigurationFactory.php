<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Services;

use SmartAssert\Compiler\Model\Options;
use Symfony\Component\Console\Input\InputInterface;
use webignition\BasilCompilerModels\Configuration;
use webignition\BasilCompilerModels\ConfigurationInterface;

class ConfigurationFactory
{
    public function create(InputInterface $input): ConfigurationInterface
    {
        $rawSource = $input->getOption(Options::OPTION_SOURCE);
        $rawSource = is_string($rawSource) ? trim($rawSource) : '';

        $rawTarget = $input->getOption(Options::OPTION_TARGET);
        $rawTarget = is_string($rawTarget) ? trim($rawTarget) : '';

        $baseClass = $input->getOption(Options::OPTION_BASE_CLASS);
        $baseClass = is_string($baseClass) ? trim($baseClass) : '';

        return new Configuration($rawSource, $rawTarget, $baseClass);
    }
}
