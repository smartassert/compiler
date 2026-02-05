<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Functional\Command;

use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\Compiler\Command\GenerateCommand;
use SmartAssert\Compiler\ExitCode;
use SmartAssert\Compiler\Model\DependencyVariables;
use SmartAssert\Compiler\Services\CommandFactory;
use SmartAssert\Compiler\Services\CompiledClassResolver;
use SmartAssert\Compiler\Services\Compiler;
use SmartAssert\Compiler\Tests\AbstractEndToEndFailureTestCase;
use SmartAssert\Compiler\Tests\Model\CliArguments;
use SmartAssert\Compiler\Tests\Model\CompilationOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedContentException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStatementException;
use webignition\BasilCompilableSourceFactory\Exception\UnsupportedStepException;
use webignition\BasilCompilerModels\Factory\ErrorOutputFactory;
use webignition\BasilCompilerModels\Model\ErrorOutput;
use webignition\BasilModels\Model\Statement\Action\ActionCollection;
use webignition\BasilModels\Model\Statement\Assertion\AssertionCollection;
use webignition\BasilModels\Model\Step\Step;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;
use webignition\ObjectReflector\ObjectReflector;

class GenerateCommandFailureTest extends AbstractEndToEndFailureTestCase
{
    /**
     * @param array<mixed> $expectedErrorOutputData
     */
    #[DataProvider('unresolvedPlaceholderDataProvider')]
    public function testRunFailure(
        string $sourceRelativePath,
        int $expectedExitCode,
        string $expectedErrorOutputMessage,
        int $expectedErrorOutputCode,
        array $expectedErrorOutputData,
        ?callable $initializer = null
    ): void {
        $cliArguments = new CliArguments(
            $this->getRemoteSourcePrefix() . $sourceRelativePath,
            $this->getRemoteTarget(),
        );

        $compilationOutput = $this->getCompilationOutput($cliArguments, $initializer);
        self::assertSame($expectedExitCode, $compilationOutput->getExitCode());

        $outputContent = trim($compilationOutput->getOutputContent());
        self::assertSame('', $outputContent);

        $errorContent = trim($compilationOutput->getErrorContent());
        $errorDocuments = $this->processYamlCollectionOutput($errorContent);
        self::assertCount(1, $errorDocuments);

        $errorDocument = $errorDocuments[0];

        $expectedErrorOutput = new ErrorOutput(
            $this->replaceConfigurationPlaceholdersInString($expectedErrorOutputMessage),
            $expectedErrorOutputCode,
            $expectedErrorOutputData
        );

        $errorOutput = new ErrorOutputFactory()->create((array) $errorDocument->parse());
        self::assertEquals($expectedErrorOutput, $errorOutput);
    }

    /**
     * @return array<mixed>
     */
    public static function unresolvedPlaceholderDataProvider(): array
    {
        return [
            'placeholder CLIENT is not defined' => [
                'sourceRelativePath' => '/Test/example.com.verify-open-literal.yml',
                'expectedExitCode' => ExitCode::UNRESOLVED_PLACEHOLDER->value,
                'expectedErrorOutputMessage' => 'Unresolved variable "CLIENT" in template '
                    . '"{{ CLIENT }}->request(\'GET\', \'https://example.com/\');"',
                'expectedErrorOutputCode' => ExitCode::UNRESOLVED_PLACEHOLDER->value,
                'expectedErrorOutputData' => [
                    'placeholder' => 'CLIENT',
                    'content' => '{{ CLIENT }}->request(\'GET\', \'https://example.com/\');',
                ],
                'initializer' => function (GenerateCommand $command) {
                    $mockExternalVariableIdentifiers = \Mockery::mock(DependencyVariables::class);
                    $mockExternalVariableIdentifiers
                        ->shouldReceive('get')
                        ->andReturn([])
                    ;

                    self::mockCompilerCompiledClassResolverExternalVariableIdentifiers(
                        $command,
                        $mockExternalVariableIdentifiers
                    );
                },
            ],
        ];
    }

    /**
     * @param array<mixed> $expectedErrorOutputContext
     */
    #[DataProvider('runFailureUnsupportedStepDataProvider')]
    public function testRunFailureUnsupportedStepException(
        UnsupportedStepException $unsupportedStepException,
        array $expectedErrorOutputContext
    ): void {
        $root = getcwd();

        $cliArguments = new CliArguments(
            $root . '/tests/Fixtures/basil/Test/example.com.verify-open-literal.yml',
            $root . '/tests/build/target'
        );

        $compilationOutput = $this->getCompilationOutput(
            $cliArguments,
            function (GenerateCommand $command) use ($unsupportedStepException) {
                $compiler = \Mockery::mock(Compiler::class);
                $compiler
                    ->shouldReceive('compile')
                    ->andThrow($unsupportedStepException)
                ;

                ObjectReflector::setProperty(
                    $command,
                    GenerateCommand::class,
                    'compiler',
                    $compiler
                );
            }
        );

        self::assertSame(ExitCode::UNSUPPORTED_STEP->value, $compilationOutput->getExitCode());

        $outputContent = trim($compilationOutput->getOutputContent());
        self::assertSame('', $outputContent);

        $expectedErrorOutput = new ErrorOutput(
            'Unsupported step',
            ExitCode::UNSUPPORTED_STEP->value,
            $expectedErrorOutputContext
        );

        $errorOutput = (new ErrorOutputFactory())->create((array) Yaml::parse($compilationOutput->getErrorContent()));

        self::assertEquals($expectedErrorOutput, $errorOutput);
    }

    /**
     * @return array<mixed>
     */
    public static function runFailureUnsupportedStepDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        return [
            'click action with attribute identifier' => [
                'unsupportedStepException' => new UnsupportedStepException(
                    new Step(
                        new ActionCollection([
                            $actionParser->parse('click $".selector".attribute_name', 0),
                        ]),
                        new AssertionCollection([]),
                    ),
                    new UnsupportedStatementException(
                        $actionParser->parse('click $".selector".attribute_name', 0),
                        new UnsupportedContentException(
                            UnsupportedContentException::TYPE_IDENTIFIER,
                            '$".selector".attribute_name'
                        )
                    )
                ),
                'expectedErrorOutputContext' => [
                    'statement_type' => 'action',
                    'statement' => 'click $".selector".attribute_name',
                    'content_type' => 'identifier',
                    'content' => '$".selector".attribute_name',
                ],
            ],
            'comparison assertion examined value identifier cannot be extracted' => [
                'unsupportedStepException' => new UnsupportedStepException(
                    new Step(
                        new ActionCollection([]),
                        new AssertionCollection([
                            $assertionParser->parse('$".selector" is "value"', 0),
                        ]),
                    ),
                    new UnsupportedStatementException(
                        $assertionParser->parse('$".selector" is "value"', 0),
                        new UnsupportedContentException(
                            UnsupportedContentException::TYPE_IDENTIFIER,
                            '$".selector"'
                        )
                    )
                ),
                'expectedErrorOutputContext' => [
                    'statement_type' => 'assertion',
                    'statement' => '$".selector" is "value"',
                    'content_type' => 'identifier',
                    'content' => '$".selector"',
                ],
            ],
            'comparison assertion examined value is not supported' => [
                'unsupportedStepException' => new UnsupportedStepException(
                    new Step(
                        new ActionCollection([]),
                        new AssertionCollection([
                            $assertionParser->parse('$elements.element_name is "value"', 0),
                        ])
                    ),
                    new UnsupportedStatementException(
                        $assertionParser->parse('$elements.element_name is "value"', 0),
                        new UnsupportedContentException(
                            UnsupportedContentException::TYPE_VALUE,
                            '$elements.element_name'
                        )
                    )
                ),
                'expectedErrorOutputContext' => [
                    'statement_type' => 'assertion',
                    'statement' => '$elements.element_name is "value"',
                    'content_type' => 'value',
                    'content' => '$elements.element_name',
                ],
            ],
            'unsupported action type' => [
                'unsupportedStepException' => new UnsupportedStepException(
                    new Step(
                        new ActionCollection([
                            $actionParser->parse('foo $".selector"', 0),
                        ]),
                        new AssertionCollection([]),
                    ),
                    new UnsupportedStatementException(
                        $actionParser->parse('foo $".selector"', 0)
                    )
                ),
                'expectedErrorOutputContext' => [
                    'statement_type' => 'action',
                    'statement' => 'foo $".selector"',
                ],
            ],
        ];
    }

    protected function getRemoteSourcePrefix(): string
    {
        return getcwd() . '/tests/Fixtures/basil';
    }

    protected function getRemoteTarget(): string
    {
        return getcwd() . '/tests/build/target';
    }

    protected function getCompilationOutput(
        CliArguments $cliArguments,
        ?callable $initializer = null
    ): CompilationOutput {
        $stdout = new BufferedOutput();
        $stderr = new BufferedOutput();
        $command = CommandFactory::createGenerateCommand($stdout, $stderr, $cliArguments->toArgvArray());

        if (null !== $initializer) {
            $initializer($command);
        }

        $exitCode = $command->run(new ArrayInput($cliArguments->getOptions()), $stderr);

        return new CompilationOutput($stdout->fetch(), $stderr->fetch(), $exitCode);
    }

    private static function mockCompilerCompiledClassResolverExternalVariableIdentifiers(
        GenerateCommand $command,
        DependencyVariables $updatedExternalVariableIdentifiers
    ): void {
        $compiledClassResolver = CompiledClassResolver::createResolver($updatedExternalVariableIdentifiers);
        $compiler = ObjectReflector::getProperty($command, 'compiler');
        \assert($compiler instanceof Compiler);

        ObjectReflector::setProperty(
            $compiler,
            Compiler::class,
            'compiledClassResolver',
            $compiledClassResolver
        );

        ObjectReflector::setProperty(
            $command,
            GenerateCommand::class,
            'compiler',
            $compiler
        );
    }
}
