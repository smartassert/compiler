<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Services;

use SmartAssert\Compiler\Model\DependencyVariables;

class DependencyVariablesFactory
{
    public static function create(): DependencyVariables
    {
        return new DependencyVariables(
            '$this->navigator',
            '$_ENV',
            'self::$client',
            'self::$crawler',
            '$this',
            'self::$inspector',
            'self::$mutator',
            'self::$messageFactory',
        );
    }
}
