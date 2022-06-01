<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Handler\Step;

use SmartAssert\Compiler\CompilableSource\CallFactory\StatementFactoryCallFactory;
use SmartAssert\Compiler\CompilableSource\Model\Body\Body;
use SmartAssert\Compiler\CompilableSource\Model\Body\BodyInterface;
use SmartAssert\Compiler\CompilableSource\Model\Expression\AssignmentExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ObjectPropertyAccessExpression;
use SmartAssert\Compiler\CompilableSource\Model\SingleLineComment;
use SmartAssert\Compiler\CompilableSource\Model\Statement\Statement;
use SmartAssert\Compiler\CompilableSource\Model\Statement\StatementInterface;
use SmartAssert\Compiler\CompilableSource\Model\VariableDependency;
use SmartAssert\Compiler\CompilableSource\VariableNames;
use webignition\BasilModels\Model\EncapsulatingStatementInterface;
use webignition\BasilModels\Model\StatementInterface as StatementModelInterface;

class StatementBlockFactory
{
    public function __construct(
        private StatementFactoryCallFactory $statementFactoryCallFactory
    ) {
    }

    public static function createFactory(): self
    {
        return new StatementBlockFactory(
            StatementFactoryCallFactory::createFactory()
        );
    }

    public function create(StatementModelInterface $statement): BodyInterface
    {
        $statementCommentContent = $statement->getSource();

        if ($statement instanceof EncapsulatingStatementInterface) {
            $statementCommentContent .= ' <- ' . $statement->getSourceStatement()->getSource();
        }

        return new Body([
            new SingleLineComment($statementCommentContent),
            $this->createAddToHandledStatementsStatement($statement),
        ]);
    }

    private function createAddToHandledStatementsStatement(StatementModelInterface $statement): StatementInterface
    {
        return new Statement(
            new AssignmentExpression(
                new ObjectPropertyAccessExpression(
                    new VariableDependency(VariableNames::PHPUNIT_TEST_CASE),
                    'handledStatements[]'
                ),
                $this->statementFactoryCallFactory->create($statement)
            )
        );
    }
}
