<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource\Model;

/**
 * @implements \IteratorAggregate<string, VariableDependencyInterface>
 */
class VariableDependencyCollection implements \IteratorAggregate
{
    /**
     * @var array<string, VariableDependencyInterface>
     */
    private array $dependencies = [];

    /**
     * @param string[] $names
     */
    public function __construct(array $names = [])
    {
        foreach ($names as $name) {
            if (is_string($name)) {
                $this->add(new VariableDependency($name));
            }
        }
    }

    public function merge(VariableDependencyCollection $collection): VariableDependencyCollection
    {
        $new = clone $this;

        foreach ($collection as $dependency) {
            $new->add($dependency);
        }

        return $new;
    }

    public function add(VariableDependencyInterface $dependency): void
    {
        $name = $dependency->getName();

        if (!array_key_exists($name, $this->dependencies)) {
            $this->dependencies[$name] = $dependency;
        }
    }

    /**
     * @return \Traversable<string, VariableDependencyInterface>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->dependencies);
    }
}
