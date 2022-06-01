<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\CompilableSource;

use webignition\BasilModels\Model\Test\TestInterface;

class ClassNameFactory
{
    public function create(TestInterface $test): string
    {
        return sprintf('Generated%sTest', ucfirst($this->createHash($test)));
    }

    private function createHash(TestInterface $test): string
    {
        $configuration = $test->getConfiguration();

        $hashComponents = [
            'path' => $test->getPath(),
            'config' => [
                'browser' => $configuration->getBrowser(),
            ],
        ];

        return md5((string) json_encode($hashComponents));
    }
}
