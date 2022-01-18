<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Model;

use SmartAssert\Compiler\Model\Options;

class CliArguments
{
    public function __construct(
        private string $source,
        private string $target,
    ) {
    }

    public function __toString(): string
    {
        return implode(' ', $this->toArgvArray());
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @return string[]
     */
    public function getOptions(): array
    {
        return [
            '--' . Options::OPTION_SOURCE => $this->source,
            '--' . Options::OPTION_TARGET => $this->target,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function toArgvArray(): array
    {
        $strings = [];

        foreach ($this->getOptions() as $key => $value) {
            $strings[] = $key . '=' . $value;
        }

        return $strings;
    }
}
