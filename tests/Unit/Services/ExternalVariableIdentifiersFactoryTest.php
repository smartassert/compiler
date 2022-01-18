<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Services;

use SmartAssert\Compiler\Model\ExternalVariableIdentifiers;
use SmartAssert\Compiler\Services\ExternalVariableIdentifiersFactory;
use SmartAssert\Compiler\Tests\Unit\AbstractBaseTest;

class ExternalVariableIdentifiersFactoryTest extends AbstractBaseTest
{
    public function testCreate(): void
    {
        self::assertEquals(
            new ExternalVariableIdentifiers(
                '$this->navigator',
                '$_ENV',
                'self::$client',
                'self::$crawler',
                '$this',
                'self::$inspector',
                'self::$mutator',
                '$this->actionFactory',
                '$this->assertionFactory'
            ),
            ExternalVariableIdentifiersFactory::create()
        );
    }
}
