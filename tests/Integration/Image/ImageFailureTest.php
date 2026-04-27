<?php

declare(strict_types=1);

namespace SmartAssert\Compiler\Tests\Integration\Image;

use SmartAssert\Compiler\Tests\AbstractEndToEndFailureTestCase;
use SmartAssert\Compiler\Tests\Model\CliArguments;
use SmartAssert\Compiler\Tests\Model\CompilationOutput;
use webignition\TcpCliProxyClient\Client;
use webignition\TcpCliProxyClient\Exception\ClientCreationException;
use webignition\TcpCliProxyClient\Exception\SocketErrorException;
use webignition\TcpCliProxyClient\Exception\SocketTimedOutException;
use webignition\TcpCliProxyClient\HandlerFactory;

class ImageFailureTest extends AbstractEndToEndFailureTestCase
{
    protected function getRemoteSourcePrefix(): string
    {
        return '/app/source';
    }

    protected function getRemoteTarget(): string
    {
        return '/app/tests';
    }

    /**
     * @throws \ErrorException
     * @throws ClientCreationException
     * @throws SocketErrorException
     * @throws SocketTimedOutException
     */
    protected function getCompilationOutput(
        CliArguments $cliArguments,
        ?callable $initializer = null
    ): CompilationOutput {
        $output = '';
        $exitCode = 0;

        $handler = (new HandlerFactory())->createWithScalarOutput($output, $exitCode);
        \assert(is_int($exitCode));

        $client = Client::createFromHostAndPort('localhost', 8000);
        $client->request('./compiler ' . $cliArguments, $handler);

        return new CompilationOutput('', $output, $exitCode);
    }
}
