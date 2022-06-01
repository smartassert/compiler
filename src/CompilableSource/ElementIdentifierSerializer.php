<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource;

use webignition\DomElementIdentifier\ElementIdentifier;
use webignition\DomElementIdentifier\ElementIdentifierInterface;

class ElementIdentifierSerializer
{
    public static function createSerializer(): self
    {
        return new ElementIdentifierSerializer();
    }

    public function serialize(ElementIdentifierInterface $elementIdentifier, int $indentDepth = 0): string
    {
        $elementOnlyIdentifier = ElementIdentifier::fromAttributeIdentifier($elementIdentifier);
        $serializedSourceIdentifier = (string) json_encode($elementOnlyIdentifier, JSON_PRETTY_PRINT);

        if ($indentDepth > 0) {
            $indent = str_repeat('    ', $indentDepth);

            $serializedSourceIdentifierLines = explode("\n", $serializedSourceIdentifier);
            array_walk($serializedSourceIdentifierLines, function (&$line) use ($indent) {
                $line = $indent . $line;
            });

            $serializedSourceIdentifier = implode("\n", $serializedSourceIdentifierLines);
        }

        return $serializedSourceIdentifier;
    }
}
