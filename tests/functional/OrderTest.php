<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;


class OrderTest extends ShopApiTest
{
    public function testFetchOrder()
    {
        $shopApi = $this->getShopApiWithResultFile('get-order.json');

        $order = $shopApi->fetchOrder('1243');
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Order', $order);
        $this->assertEquals('123455', $order->getId());
        $basket = $order->getBasket();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Basket', $basket);
    }
} 