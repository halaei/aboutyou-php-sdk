<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;
use Collins\ShopApi\Model;

class BasketTest extends AbstractShopApiTest
{
    private $sessionId = null;

    public function testBasket()
    {
        $shopApi = $this->getShopApiWithResultFile('basket.json');

        $basket = $shopApi->fetchBasket('testing');
        $this->checkBasket($basket);
        
        $items = $basket->getItems();
        $this->assertCount(7, $items);

        $this->assertEquals('id1', $items[0]->getId());
        $this->assertEquals(19.0, $items[0]->getTax());
        $this->assertEquals(39.99, $items[0]->getTotalPrice());
        $this->assertEquals(3361, $items[0]->getTotalNet());
        $this->assertEquals(3361, $items[0]->getTotalVat());
        $this->assertEquals(219304, $items[0]->getProduct()->getId());
        $this->assertEquals(4719964, $items[0]->getVariant()->getId());
        $this->assertEquals([
            'date' => '2014-03-18',
            'foo' => 'bar',
            'description' => 'Very interesting article'
        ], (array) $items[0]->getAdditionalData());

        $this->assertEquals('id3', $items[2]->getId());
        $subItems = $items[6]->getBasketVariants();
        $this->assertEquals(39.99, $items[2]->getTotalPrice());
        $this->assertEquals(3361, $items[2]->getTotalNet());
        $this->assertEquals(3361, $items[2]->getTotalVat());
        $this->assertEquals(19.0, $subItems[0]->getTax());
        $this->assertEquals(24.5, $subItems[0]->getTotalPrice());
        $this->assertEquals(2059, $subItems[0]->getTotalNet());
        $this->assertEquals(2059, $subItems[0]->getTotalVat());
        $this->assertEquals(219287, $subItems[0]->getProduct()->getId());
        $this->assertEquals(4719841, $subItems[0]->getVariant()->getId());
        $this->assertNotNull($subItems[0]->getAdditionalData());

        return $basket;
    }

    /**
     *
     */
    public function testAddToBasket()
    {
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"id":"item1","variant_id":123}]}}]';
        $shopApi = $this->getShopApiWithResultFile('basket.json', $exceptedRequestBody);

        // add one item to basket
        $item = new ShopApi\Model\BasketItem(123);
        $item->setId('item1');
        $basket = $shopApi->addItemToBasket('testing', $item);
        $this->checkBasket($basket);

        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"id":"item2","variant_id":123}]}}]';
        $shopApi = $this->getShopApiWithResultFile('basket.json', $exceptedRequestBody);
        
        $item2 = new ShopApi\Model\BasketItem(123);
        $item2->setId('item2');
        $basket = $shopApi->addItemToBasket('testing', $item2);
        $this->checkBasket($basket);
    }

    /**
     *
     */
    public function testRemoveFromBasket()
    {
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"delete":"item3"}]}}]';

        $shopApi = $this->getShopApiWithResultFile('basket.json', $exceptedRequestBody);

        // remove all of one item from basket
        $basket = $shopApi->removeFromBasket('testing', ['item3']);
        $this->checkBasket($basket);
    }

    /**
     * Check if given object is a valid basket.
     */
    private function checkBasket(Model\Basket $basket)
    {
        $this->assertInstanceOf('Collins\ShopApi\Model\Basket', $basket);
        $this->assertInternalType('int', $basket->getTotalPrice());
        $this->assertInternalType('int', $basket->getTotalNet());
        $this->assertInternalType('int', $basket->getTotalVat());

        foreach ($basket->getItems() as $item) {
            $this->assertInstanceOf('Collins\ShopApi\Model\BasketObject', $item);
            if ($item->isVariant()) {
                $this->assertInstanceOf('Collins\ShopApi\Model\BasketVariant', $item);
                $this->checkBasketVariant($item);
            } else {
                $this->assertInstanceOf('Collins\ShopApi\Model\BasketVariantSet', $item);
                $this->checkVariantSet($item);
            }
        }
    }

    private function checkBasketVariant(Model\BasketVariant $item)
    {
        $this->assertInternalType('float', $item->getTotalPrice());
        $this->assertInternalType('int', $item->getTax());
        $this->assertInternalType('int', $item->getTotalNet());
        $this->assertInternalType('int', $item->getTotalVat());
        $this->assertInstanceOf('Collins\ShopApi\Model\Product', $item->getProduct());
        $this->assertInstanceOf('Collins\ShopApi\Model\Variant', $item->getVariant());

    }

    private function checkVariantSet(Model\BasketVariantSet $set)
    {
        foreach ($set->getBasketVariants() as $basketVariant) {
            $this->checkBasketVariant($basketVariant);
        }
    }
}
