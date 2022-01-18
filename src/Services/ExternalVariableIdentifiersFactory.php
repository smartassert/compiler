<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Services;

use SmartAssert\Compiler\Model\ExternalVariableIdentifiers;

class ExternalVariableIdentifiersFactory
{
    public static function create(): ExternalVariableIdentifiers
    {
        return new ExternalVariableIdentifiers(
            '$this->navigator',
            '$_ENV',
            'self::$client',
            'self::$crawler',
            '$this',
            'self::$inspector',
            'self::$mutator',
            '$this->actionFactory',
            '$this->assertionFactory'
        );
    }
}
