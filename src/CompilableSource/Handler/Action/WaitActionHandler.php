<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Handler\Action;

use SmartAssert\Compiler\CompilableSource\AccessorDefaultValueFactory;
use SmartAssert\Compiler\CompilableSource\Exception\UnsupportedContentException;
use SmartAssert\Compiler\CompilableSource\Model\Body\Body;
use SmartAssert\Compiler\CompilableSource\Model\Body\BodyInterface;
use SmartAssert\Compiler\CompilableSource\Model\Expression\CastExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\ComparisonExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\CompositeExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\EncapsulatedExpression;
use SmartAssert\Compiler\CompilableSource\Model\Expression\LiteralExpression;
use SmartAssert\Compiler\CompilableSource\Model\MethodArguments\MethodArguments;
use SmartAssert\Compiler\CompilableSource\Model\MethodInvocation\MethodInvocation;
use SmartAssert\Compiler\CompilableSource\ValueAccessorFactory;
use webignition\BasilModels\Model\Action\ActionInterface;

class WaitActionHandler
{
    private const MICROSECONDS_PER_MILLISECOND = 1000;

    public function __construct(
        private AccessorDefaultValueFactory $accessorDefaultValueFactory,
        private ValueAccessorFactory $valueAccessorFactory
    ) {
    }

    public static function createHandler(): WaitActionHandler
    {
        return new WaitActionHandler(
            AccessorDefaultValueFactory::createFactory(),
            ValueAccessorFactory::createFactory()
        );
    }

    /**
     * @throws UnsupportedContentException
     */
    public function handle(ActionInterface $waitAction): BodyInterface
    {
        $duration = (string) $waitAction->getValue();
        if (ctype_digit($duration)) {
            $duration = '"' . $duration . '"';
        }

        $durationAccessor = $this->valueAccessorFactory->create($duration);

        $nullCoalescingExpression = new ComparisonExpression(
            $durationAccessor,
            new LiteralExpression((string) ($this->accessorDefaultValueFactory->createInteger($duration) ?? 0)),
            '??'
        );

        $castToIntExpression = new CastExpression($nullCoalescingExpression, 'int');

        $sleepInvocation = new MethodInvocation(
            'usleep',
            new MethodArguments(
                [
                    new CompositeExpression([
                        new EncapsulatedExpression($castToIntExpression),
                        new LiteralExpression(' * '),
                        new LiteralExpression((string) self::MICROSECONDS_PER_MILLISECOND)
                    ]),
                ]
            )
        );

        return Body::createFromExpressions([$sleepInvocation]);
    }
}
