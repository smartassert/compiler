<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Services;

class ProjectRootPathProvider
{
    public static function create(): ProjectRootPathProvider
    {
        return new ProjectRootPathProvider();
    }

    public function get(): string
    {
        return (string) realpath(__DIR__ . '/../..');
    }
}
