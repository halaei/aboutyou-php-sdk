<?php
namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class SuggestTest extends ShopApiTest
{
    /**
     *
     */
    public function testSuggest()
    {
        $shopApi = $this->getShopApiWithResultFile('suggest.json');

        $suggestions = $shopApi->fetchSuggest('hose');
        $this->assertInternalType('array', $suggestions);
    }
}
