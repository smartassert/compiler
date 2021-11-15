<?php

declare(strict_types=1);

namespace webignition\BasilCliCompiler\Tests\Integration\Image;

use webignition\BasilCliCompiler\Tests\AbstractEndToEndSuccessTest;
use webignition\BasilCliCompiler\Tests\Model\CliArguments;
use webignition\BasilCliCompiler\Tests\Model\CompilationOutput;
use webignition\TcpCliProxyClient\Client;
use webignition\TcpCliProxyClient\HandlerFactory;

class ImageSuccessTest extends AbstractEndToEndSuccessTest
{
    protected function getRemoteSourcePrefix(): string
    {
        return '/app/source';
    }

    protected function getRemoteTarget(): string
    {
        return '/app/tests';
    }

    protected function getCompilationOutput(
        CliArguments $cliArguments,
        ?callable $initializer = null
    ): CompilationOutput {
        $output = '';
        $exitCode = 0;

        $handler = (new HandlerFactory())->createWithScalarOutput($output, $exitCode);

        $client = Client::createFromHostAndPort('localhost', 8000);
        $client->request('./compiler ' . $cliArguments, $handler);

        return new CompilationOutput($output, $exitCode);
    }
}
