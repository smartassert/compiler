<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Command;

use SmartAssert\Compiler\Model\Options;
use SmartAssert\Compiler\Services\Compiler;
use SmartAssert\Compiler\Services\ConfigurationFactory;
use SmartAssert\Compiler\Services\ErrorOutputFactory;
use SmartAssert\Compiler\Services\OutputRenderer;
use SmartAssert\Compiler\Services\TestWriter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface as ConsoleOutputInterface;
use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilerModels\Configuration;
use webignition\BasilCompilerModels\TestManifest;
use webignition\BasilLoader\Exception\EmptyTestException;
use webignition\BasilLoader\Exception\InvalidPageException;
use webignition\BasilLoader\Exception\InvalidTestException;
use webignition\BasilLoader\Exception\NonRetrievableImportException;
use webignition\BasilLoader\Exception\ParseException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilLoader\Resolver\CircularStepImportException;
use webignition\BasilLoader\Resolver\UnknownElementException;
use webignition\BasilLoader\Resolver\UnknownPageElementException;
use webignition\BasilLoader\TestLoader;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\Stubble\UnresolvedVariableException;

class GenerateCommand extends Command
{
    private const NAME = 'generate';

    public function __construct(
        private TestLoader $testLoader,
        private Compiler $compiler,
        private TestWriter $testWriter,
        private ErrorOutputFactory $errorOutputFactory,
        private OutputRenderer $outputRenderer,
        private ConfigurationFactory $configurationFactory
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generate tests from basil source')
            ->addOption(
                Options::OPTION_SOURCE,
                null,
                InputOption::VALUE_REQUIRED,
                'Path to the basil test source from which to generate tests. ' .
                'Can be absolute or relative to this directory.',
                ''
            )
            ->addOption(
                Options::OPTION_TARGET,
                null,
                InputOption::VALUE_REQUIRED,
                'Output path for generated tests',
                ''
            )
            ->addOption(
                Options::OPTION_BASE_CLASS,
                null,
                InputOption::VALUE_OPTIONAL,
                'Base class to extend',
                AbstractBaseTest::class
            )
        ;
    }

    protected function execute(InputInterface $input, ConsoleOutputInterface $output): int
    {
        $configuration = $this->configurationFactory->create($input);

        $this->outputRenderer->renderConfiguration($configuration);

        $configurationValidationState = $configuration->validate();
        if (Configuration::VALIDATION_STATE_VALID !== $configurationValidationState) {
            $errorOutput = $this->errorOutputFactory->createFromInvalidConfiguration($configurationValidationState);

            $this->outputRenderer->renderErrorOutput($errorOutput);

            return $errorOutput->getCode();
        }

        $testManifests = [];

        try {
            $tests = $this->testLoader->load($configuration->getSource());
        } catch (
            CircularStepImportException |
            EmptyTestException |
            InvalidPageException |
            InvalidTestException |
            NonRetrievableImportException |
            ParseException |
            UnknownElementException |
            UnknownItemException |
            UnknownPageElementException |
            YamlLoaderException $exception
        ) {
            $errorOutput = $this->errorOutputFactory->createForException($exception);

            $this->outputRenderer->renderErrorOutput($errorOutput);

            return $errorOutput->getCode();
        }

        try {
            foreach ($tests as $test) {
                $compiledTest = $this->compiler->compile($test, $configuration->getBaseClass());
                $target = $this->testWriter->write($compiledTest, $configuration->getTarget());

                $testManifests[] = new TestManifest(
                    $test->getConfiguration()->getBrowser(),
                    $test->getConfiguration()->getUrl(),
                    $test->getPath() ?? '',
                    $target,
                    $test->getSteps()->getStepNames()
                );
            }
        } catch (
            UnresolvedVariableException |
            UnsupportedStepException $exception
        ) {
            $errorOutput = $this->errorOutputFactory->createForException($exception);

            $this->outputRenderer->renderErrorOutput($errorOutput);

            return $errorOutput->getCode();
        }

        $this->outputRenderer->renderTestManifests($testManifests);

        return 0;
    }
}
