<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\Test\Functional;

use Collins\ShopApi;

class ShopApiTest extends \PHPUnit_Framework_TestCase
{
    public function testShopApi()
    {
        $api = new ShopApi('key', 'token');
    }
}
