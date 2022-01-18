<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Model;

/**
 * @implements \ArrayAccess<int, ExpectedGeneratedTest>
 */
class ExpectedGeneratedTestCollection implements \ArrayAccess, \Countable
{
    /**
     * @param ExpectedGeneratedTest[] $expectedGeneratedTests
     */
    public function __construct(
        private array $expectedGeneratedTests
    ) {
    }

    /**
     * @return string[]
     */
    public function getReplacementClassNames(): array
    {
        $names = [];

        foreach ($this->expectedGeneratedTests as $expectedGeneratedTest) {
            $names[] = $expectedGeneratedTest->getReplacementClassName();
        }

        return $names;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->expectedGeneratedTests);
    }

    public function offsetGet(mixed $offset): ExpectedGeneratedTest
    {
        return $this->expectedGeneratedTests[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->expectedGeneratedTests[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->expectedGeneratedTests[$offset]);
    }

    public function count(): int
    {
        return count($this->expectedGeneratedTests);
    }
}
