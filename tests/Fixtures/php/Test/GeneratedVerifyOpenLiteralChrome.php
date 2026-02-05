<?php

namespace SmartAssert\Compiler\Generated;

use webignition\BaseBasilTestCase\AbstractBaseTest;
use webignition\BaseBasilTestCase\Attribute\Statements;
use webignition\BaseBasilTestCase\Attribute\StepName;
use webignition\BaseBasilTestCase\ClientManager;
use webignition\BaseBasilTestCase\Enum\StatementStage;

class GeneratedVerifyOpenLiteralChrome extends AbstractBaseTest
{
    public static function setUpBeforeClass(): void
    {
        self::setClientManager(new ClientManager('chrome'));
        parent::setUpBeforeClass();
        self::$client->request('GET', 'https://example.com/');
    }

    #[StepName('verify page is open')]
    #[Statements([
        '{
            "statement-type": "assertion",
            "source": "$page.url is \"https:\/\/example.com\/\"",
            "index": 0,
            "identifier": "$page.url",
            "value": "\"https:\/\/example.com\/\"",
            "operator": "is"
        }',
    ])]
    public function test1(): void
    {
        // $page.url is "https://example.com/"
        $statement_0 = '{
            "statement-type": "assertion",
            "source": "$page.url is \"https:\/\/example.com\/\"",
            "index": 0,
            "identifier": "$page.url",
            "value": "\"https:\/\/example.com\/\"",
            "operator": "is"
        }';

        try {
            $expectedValue = "https://example.com/";
            $examinedValue = self::$client->getCurrentURL();
        } catch (\Throwable $exception) {
            $this->fail(
                self::$messageFactory->createFailureMessage($statement_0, $exception, StatementStage::SETUP),
            );
        }

        $this->assertEquals(
            $expectedValue,
            $examinedValue,
            self::$messageFactory->createAssertionMessage($statement_0, $expectedValue, $examinedValue),
        );
    }
}
