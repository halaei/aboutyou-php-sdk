<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;


class OrderTestAbstract extends AbstractShopApiTest
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

    public function testInitiateOrderSuccess()
    {
        $shopApi = $this->getShopApiWithResultFile('initiate-order.json');
        $initiateOrder = $shopApi->initiateOrder(
            "abcabcabc",
            "http://somedomain.com/url"
        );
        $this->assertEquals(
            'http://ant-web1.wavecloud.de/?user_token=34f9b86d-c899-4703-b85a-3c4971601b59&app_token=10268cc8-2025-4285-8e17-bc3160865824',
            $initiateOrder->getUrl()
        );
        $this->assertEquals(
            '34f9b86d-c899-4703-b85a-3c4971601b59',
            $initiateOrder->getUserToken()
        );
        $this->assertEquals(
            '10268cc8-2025-4285-8e17-bc3160865824',
            $initiateOrder->getAppToken()
        );
    }

    public function testInitiateOrderWithCancelAndErrorUrls()
    {
        $shopApi = $this->getShopApiWithResultFile('initiate-order.json');
        $initiateOrder = $shopApi->initiateOrder(
            "abcabcabc",
            "http://somedomain.com/url",
            "http://somedomain.com/cancel",
            "http://somedomain.com/error"
        );
        $this->assertInternalType('string', $initiateOrder->getUrl());
    }

    public function testInitiateOrderVerifyUrl()
    {
        $this->markTestIncomplete('implement me');
    }

    public function testInitiateOrderFailed()
    {
        $this->markTestIncomplete('implement me');
    }
}