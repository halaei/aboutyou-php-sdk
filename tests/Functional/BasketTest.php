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
        $this->assertTrue($basket->hasErrors());

        $items = $basket->getItems();
        $this->assertCount(2, $items);

        $this->assertEquals('identifier1', $items[0]->getId());
        $this->assertFalse($items[0]->hasErrors());
        $this->assertEquals(19.0, $items[0]->getTax());
        $this->assertEquals(400, $items[0]->getPrice());
        $this->assertEquals(390, $items[0]->getNet());
        $this->assertEquals(10, $items[0]->getVat());
        $this->assertEquals(123, $items[0]->getProduct()->getId());
        $this->assertEquals(1543435, $items[0]->getVariant()->getId());
        $this->assertNull($items[0]->getAdditionalData());
        $this->assertNull($items[0]->getDescription());

        $this->assertEquals('identifier3', $items[2]->getId());
        $subItems = $items[2]->getItems();
        $this->assertFalse($subItems[0]->hasErrors());
        $this->assertEquals(19.0, $subItems[0]->getTax());
        $this->assertEquals(600, $subItems[0]->getPrice());
        $this->assertEquals(590, $subItems[0]->getNet());
        $this->assertEquals(10, $subItems[0]->getVat());
        $this->assertEquals(123, $subItems[0]->getProduct()->getId());
        $this->assertEquals(12312121, $subItems[0]->getVariant()->getId());
        $this->assertNotNull($subItems[0]->getAdditionalData());
        $this->assertEquals('engravingssens', $subItems[0]->getDescription());
        $this->assertEquals(['stuff'], $subItems[0]->getCustomData());

//        $this->assertEquals('identifier2', $items[1]->getId());
//        $this->assertTrue($items[1]->hasErrors());

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
            $this->assertInstanceOf('Collins\ShopApi\Model\Basket\BasketItemInterface', $item);
            if ($item instanceof Basket\BasketItem) {
                $this->assertInstanceOf('Collins\ShopApi\Model\Basket\BasketItem', $item);
                $this->checkBasketVariantItem($item);
            } else {
                $this->assertInstanceOf('Collins\ShopApi\Model\Basket\BasketSet', $item);
                $this->checkBasketSet($item);
            }
        }
    }

    private function checkBasketVariantItem(Basket\BasketVariantItem $item)
    {
        $this->assertInternalType('int', $item->getPrice());
        $this->assertInternalType('float', $item->getTax());
        $this->assertInternalType('int', $item->getNet());
        $this->assertInternalType('int', $item->getVat());
        $this->assertInstanceOf('Collins\ShopApi\Model\Product', $item->getProduct());
        $this->assertInstanceOf('Collins\ShopApi\Model\Variant', $item->getVariant());

    }

    private function checkBasketSet(Basket\BasketSet $set)
    {
        foreach ($set->getItems() as $item) {
            $this->checkBasketVariantItem($item);
        }
    }
}
