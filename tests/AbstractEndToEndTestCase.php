<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests;

use PHPUnit\Framework\TestCase;
use SmartAssert\Compiler\Tests\Model\CliArguments;
use SmartAssert\Compiler\Tests\Model\CompilationOutput;
use webignition\YamlDocument\Document;
use webignition\YamlDocument\Factory;

abstract class AbstractEndToEndTestCase extends TestCase
{
    abstract protected function getRemoteSourcePrefix(): string;

    abstract protected function getRemoteTarget(): string;

    abstract protected function getCompilationOutput(
        CliArguments $cliArguments,
        ?callable $initializer = null
    ): CompilationOutput;

    /**
     * @return Document[]
     */
    protected function processYamlCollectionOutput(string $content): array
    {
        /**
         * @var Document[]
         */
        $documents = [];
        $yamlDocumentFactory = new Factory();
        $yamlDocumentFactory->reset(function (Document $document) use (&$documents) {
            $documents[] = $document;
        });

        $yamlDocumentFactory->process($content);
        $yamlDocumentFactory->stop();

        return $documents;
    }
}
