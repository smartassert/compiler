<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Services;

use SmartAssert\Compiler\Model\DependencyVariables;
use SmartAssert\Compiler\Services\DependencyVariablesFactory;
use SmartAssert\Compiler\Tests\Unit\AbstractBaseTestCase;

class DependencyVariablesFactoryTest extends AbstractBaseTestCase
{
    public function testCreate(): void
    {
        self::assertEquals(
            new DependencyVariables(
                '$this->navigator',
                '$_ENV',
                'self::$client',
                'self::$crawler',
                '$this',
                'self::$inspector',
                'self::$mutator',
                'self::$messageFactory',
            ),
            DependencyVariablesFactory::create()
        );
    }
}
