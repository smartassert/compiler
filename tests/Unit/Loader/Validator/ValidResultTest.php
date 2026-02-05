<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Unit\Loader\Validator;

use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Loader\Validator\ValidResult;

class ValidResultTest extends TestCase
{
    public function testCreate(): void
    {
        $subject = new \stdClass();

        $result = new ValidResult($subject);

        $this->assertTrue($result->getIsValid());
        $this->assertSame($subject, $result->getSubject());
    }
}
