<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;
use Collins\ShopApi\Model\Basket;

class BasketTestAbstract extends AbstractShopApiTest
{
    private $sessionId = null;

    /**
     *
     */
    public function setUp()
    {
        $this->sessionId = 'testing';
    }

    /**
     *
     */
    public function testBasket()
    {
        $exceptedRequestBody = '[{"basket":{"session_id":"testing"}}]';
        $shopApi = $this->getShopApiWithResultFile('result/basket1.json', $exceptedRequestBody);

        $basket = $shopApi->fetchBasket($this->sessionId);
        $this->checkBasket($basket);

        return $basket;
    }

    /**
     *
     */
    public function testAddToBasket()
    {
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"id":"item1","variant_id":123}]}}]';
        $shopApi = $this->getShopApiWithResultFile('result/basket1.json', $exceptedRequestBody);

        // add one item to basket
        $productVariantId = 123;
        $basket = $shopApi->addToBasket($this->sessionId, $productVariantId, 'item1');
        $this->checkBasket($basket);

        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"id":"item2","variant_id":123}]}}]';
        $shopApi = $this->getShopApiWithResultFile('result/basket1.json', $exceptedRequestBody);
        // add more of one item to basket
        $productVariantId = 123;
        $basket = $shopApi->addToBasket($this->sessionId, $productVariantId, 'item2');
        $this->checkBasket($basket);
    }

    /**
     *
     */
    public function testRemoveFromBasket()
    {
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"delete":"item3"}]}}]';

        $shopApi = $this->getShopApiWithResultFile('result/basket1.json', $exceptedRequestBody);

        // remove all of one item from basket
        $basket = $shopApi->removeFromBasket($this->sessionId, 'item3');
        $this->checkBasket($basket);
    }

    /**
     * @depends testBasket
     */
    public function testUpdateBasket(Basket $basket)
    {
//        $this->markTestIncomplete('');
        $exceptedRequestBody = '[{"basket":{"session_id":"testing"}}]';

        $shopApi = $this->getShopApiWithResultFile('result/basket1.json', $exceptedRequestBody);

        $basket = $shopApi->updateBasket($this->sessionId, $basket);
        $this->checkBasket($basket);
    }

    /**
     * Check if given object is a valid basket.
     */
    private function checkBasket(Basket $basket)
    {
        $this->assertInstanceOf('Collins\ShopApi\Model\Basket', $basket);
        $this->assertInternalType('int', $basket->getTotalPrice());
        $this->assertInternalType('int', $basket->getTotalNet());
        $this->assertInternalType('int', $basket->getTotalVat());
        $this->assertInternalType('int', $basket->getTotalAmount());
        $this->assertInternalType('int', $basket->getTotalVariants());

        foreach ($basket->getItems() as $item) {
            $this->assertInstanceOf('Collins\ShopApi\Model\BasketItemInterface', $item);
            if ($item instanceof ShopApi\Model\BasketItem) {
                $this->assertInstanceOf('Collins\ShopApi\Model\BasketItem', $item);
                $this->checkBasketVariantItem($item);
            } else {
                $this->assertInstanceOf('Collins\ShopApi\Model\BasketSet', $item);
                $this->checkBasketSet($item);
            }
        }
    }

    private function checkBasketVariantItem(ShopApi\Model\BasketVariantItem $item)
    {
        $this->assertInternalType('int', $item->getPrice());
        $this->assertInternalType('float', $item->getTax());
        $this->assertInternalType('int', $item->getNet());
        $this->assertInternalType('int', $item->getVat());
        $this->assertInstanceOf('Collins\ShopApi\Model\Product', $item->getProduct());
        $this->assertInstanceOf('Collins\ShopApi\Model\Variant', $item->getVariant());

    }

    private function checkBasketSet(ShopApi\Model\BasketSet $set)
    {
        foreach ($set->getItems() as $item) {
            $this->checkBasketVariantItem($item);
        }
    }
}
