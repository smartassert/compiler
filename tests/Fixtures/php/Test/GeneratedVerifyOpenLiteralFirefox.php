<?php

namespace SmartAssert\Compiler\Generated;

use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BaseBasilTestCase\ClientManager;
use webignition\BasilModels\Model\Test\Configuration;

class GeneratedVerifyOpenLiteralFirefox extends AbstractBaseTest
{
    public static function setUpBeforeClass(): void
    {
        try {
            self::setClientManager(new ClientManager(
                new Configuration(
                    'firefox',
                    'https://example.com/'
                )
            ));
            parent::setUpBeforeClass();
            self::$client->request('GET', 'https://example.com/');
        } catch (\Throwable $exception) {
            self::staticSetLastException($exception);
        }
    }

    public function test1()
    {
        if (self::hasException()) {
            return;
        }
        $this->setBasilStepName('verify page is open');
        $this->setCurrentDataSet(null);

        // $page.url is "https://example.com/"
        $this->handledStatements[] = $this->assertionFactory->createFromJson('{
            "statement-type": "assertion",
            "source": "$page.url is \\"https:\\/\\/example.com\\/\\"",
            "identifier": "$page.url",
            "value": "\\"https:\\/\\/example.com\\/\\"",
            "operator": "is"
        }');
        $this->setExpectedValue("https://example.com/" ?? null);
        $this->setExaminedValue(self::$client->getCurrentURL() ?? null);
        $this->assertEquals(
            $this->getExpectedValue(),
            $this->getExaminedValue()
        );
    }
}
