<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource;

use SmartAssert\Compiler\CompilableSource\Exception\UnsupportedStepException;
use SmartAssert\Compiler\CompilableSource\Model\Block\TryCatch\CatchBlock;
use SmartAssert\Compiler\CompilableSource\Model\Block\TryCatch\TryBlock;
use SmartAssert\Compiler\CompilableSource\Model\Block\TryCatch\TryCatchBlock;
use SmartAssert\Compiler\CompilableSource\Model\Body\Body;
use SmartAssert\Compiler\CompilableSource\Model\ClassBody;
use SmartAssert\Compiler\CompilableSource\Model\ClassDefinition;
use SmartAssert\Compiler\CompilableSource\Model\ClassDefinitionInterface;
use SmartAssert\Compiler\CompilableSource\Model\ClassName;
use SmartAssert\Compiler\CompilableSource\Model\ClassSignature;
use SmartAssert\Compiler\CompilableSource\Model\Expression\CatchExpression;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArguments;
use SmartAssert\Compiler\CompilableSource\Model\MethodDefinition;
use SmartAssert\Compiler\CompilableSource\Model\MethodDefinitionInterface;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\ObjectConstructor;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\ObjectMethodInvocation;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\StaticObjectMethodInvocation;
use SmartAssert\Compiler\CompilableSource\Model\Statement\Statement;
use SmartAssert\Compiler\CompilableSource\Model\StaticObject;
use SmartAssert\Compiler\CompilableSource\Model\TypeDeclaration\ObjectTypeDeclaration;
use SmartAssert\Compiler\CompilableSource\Model\TypeDeclaration\ObjectTypeDeclarationCollection;
use SmartAssert\Compiler\CompilableSource\Model\VariableDependency;
use SmartAssert\Compiler\CompilableSource\Model\VariableName;
use webignition\BaseBasilTestCase\ClientManager;
use webignition\BasilModels\Model\Step\StepInterface;
use webignition\BasilModels\Model\Test\Configuration;
use webignition\BasilModels\Model\Test\TestInterface;

class ClassDefinitionFactory
{
    public function __construct(
        private ClassNameFactory $classNameFactory,
        private StepMethodFactory $stepMethodFactory,
        private ArgumentFactory $argumentFactory
    ) {
    }

    public static function createFactory(): ClassDefinitionFactory
    {
        return new ClassDefinitionFactory(
            new ClassNameFactory(),
            StepMethodFactory::createFactory(),
            ArgumentFactory::createFactory()
        );
    }

    /**
     * @throws UnsupportedStepException
     */
    public function createClassDefinition(
        TestInterface $test,
        ?string $fullyQualifiedBaseClass = null
    ): ClassDefinitionInterface {
        $methodDefinitions = [
            $this->createSetupBeforeClassMethod($test),
        ];

        $stepOrdinalIndex = 1;
        foreach ($test->getSteps() as $stepName => $step) {
            if ($step instanceof StepInterface) {
                $methodDefinitions = array_merge(
                    $methodDefinitions,
                    $this->stepMethodFactory->create($stepOrdinalIndex, $stepName, $step)
                );
                ++$stepOrdinalIndex;
            }
        }

        $baseClass = is_string($fullyQualifiedBaseClass) ? new ClassName($fullyQualifiedBaseClass) : null;

        return new ClassDefinition(
            new ClassSignature(
                $this->classNameFactory->create($test),
                $baseClass
            ),
            new ClassBody($methodDefinitions)
        );
    }

    private function createSetupBeforeClassMethod(TestInterface $test): MethodDefinitionInterface
    {
        $testConfiguration = $test->getConfiguration();

        $tryBody = new Body([
            new Statement(
                new StaticObjectMethodInvocation(
                    new StaticObject('self'),
                    'setClientManager',
                    new MethodArguments(
                        [
                            new ObjectConstructor(
                                new ClassName(ClientManager::class),
                                new MethodArguments(
                                    [
                                        new ObjectConstructor(
                                            new ClassName(Configuration::class),
                                            new MethodArguments(
                                                $this->argumentFactory->create(
                                                    $testConfiguration->getBrowser(),
                                                    $testConfiguration->getUrl()
                                                ),
                                                MethodArguments::FORMAT_STACKED
                                            )
                                        ),
                                    ],
                                    MethodArguments::FORMAT_STACKED
                                )
                            ),
                        ]
                    ),
                )
            ),
            new Statement(
                new StaticObjectMethodInvocation(
                    new StaticObject('parent'),
                    'setUpBeforeClass'
                )
            ),
            new Statement(
                new ObjectMethodInvocation(
                    new VariableDependency(VariableNames::PANTHER_CLIENT),
                    'request',
                    new MethodArguments(
                        $this->argumentFactory->create('GET', $testConfiguration->getUrl())
                    )
                )
            ),
        ]);

        $catchBody = new Body([
            new Statement(
                new StaticObjectMethodInvocation(
                    new StaticObject('self'),
                    'staticSetLastException',
                    new MethodArguments([
                        new VariableName('exception')
                    ])
                ),
            ),
        ]);

        $tryCatchBlock = new TryCatchBlock(
            new TryBlock($tryBody),
            new CatchBlock(
                new CatchExpression(
                    new ObjectTypeDeclarationCollection([
                        new ObjectTypeDeclaration(new ClassName(\Throwable::class))
                    ])
                ),
                $catchBody
            )
        );

        $method = new MethodDefinition('setUpBeforeClass', $tryCatchBlock);

        $method->setStatic();
        $method->setReturnType('void');

        return $method;
    }
}
