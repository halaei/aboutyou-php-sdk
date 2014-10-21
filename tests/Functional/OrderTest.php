<?php
/**
 * @author nils.droege@project-collins.com
 * (c) ABOUT YOU GmbH
 */

namespace Collins\ShopApi\Test\Functional;


class OrderTest extends AbstractShopApiTest
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
    
    public function testFetchOrderWithProductsWithoutCategories()
    {
        $shopApi = $this->getShopApiWithResultFile('get-order-without-categories.json');
        
        $order = $shopApi->fetchOrder('53574');
        $basket = $order->getBasket();
        $products = $basket->getProducts();
        $product = array_pop($products);

        $this->assertCount(0, $product->getCategories());
    }

    public function testInitiateOrderSuccess()
    {
        $shopApi = $this->getShopApiWithResultFile('initiate-order.json');
        $initiateOrder = $shopApi->initiateOrder(
            "abcabcabc",
            "http://somedomain.com/url"
        );
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\InitiateOrder', $initiateOrder);
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

    /**
     * @expectedException \Collins\ShopApi\Exception\ResultErrorException
     * @expectedExceptionMessage Basket is empty: abcabcabc
     */
    public function testInitiateOrderFailedWithEmptyBasket()
    {
        $shopApi = $this->getShopApiWithResult('[
            {
                "initiate_order": {
                    "error_ident": "440db3b3-75c4-4223-b5cf-e57d37616239",
                    "error_message": [
                        "Basket is empty: abcabcabc"
                    ],
                    "error_code": 400
                }
            }
        ]');
        $initiateOrder = $shopApi->initiateOrder(
            "abcabcabc",
            "http://somedomain.com/url"
        );
    }

    /**
     * @expectedException \Collins\ShopApi\Exception\ResultErrorException
     * @expectedExceptionMessage success_url: u'/checkout/success' does not match '^http(s|)://'
     */
    public function testInitiateOrderFailedWithError()
    {
        $response = <<<EOS
        [{
            "initiate_order": {
                "error_message": [ "success_url: u'/checkout/success' does not match '^http(s|)://'" ],
                "error_code": 400
            }
        }]
EOS;

        $shopApi = $this->getShopApiWithResult($response);
        $initiateOrder = $shopApi->initiateOrder(
            "abcabcabc",
            "/somedomain.com/url"
        );
    }
}