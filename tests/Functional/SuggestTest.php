<?php
namespace AboutYou\SDK\Test\Functional;

use \AY;

class SuggestTest extends AbstractShopApiTest
{
    public function testSuggest()
    {
        $shopApi = $this->getShopApiWithResultFile('suggest.json');

        $suggestions = $shopApi->fetchSuggest('hose');
        $this->assertInternalType('array', $suggestions);
        $this->assertCount(10, $suggestions);
        $this->assertEquals('fit', $suggestions[3]);
    }
}
