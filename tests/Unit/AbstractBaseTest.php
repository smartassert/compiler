<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

abstract class AbstractBaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;
}
