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
        $shopApi = $this->getShopApiWithResultFile('result/basket1.json');

        $basket = $shopApi->fetchBasket($this->sessionId);
        $this->checkBasket($basket);
    }

    /**
     *
     */
    public function testAddToBasket()
    {
        $this->markTestIncomplete('');

        $shopApi = $this->getShopApiWithResultFile('result/basket1.json');

        // add one item to basket
        $productVariantId = 123;
        $basket = $shopApi->addToBasket($this->sessionId, $productVariantId, 'item1');
        $this->checkBasket($basket);

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
        $this->markTestIncomplete('');

        $shopApi = $this->getShopApiWithResultFile('result/basket1.json');

        // remove all of one item from basket
        $productVariantId = 123;
        $basket = $shopApi->removeFromBasket($this->sessionId, $productVariantId, 'item3');
        $this->checkBasket($basket);
    }

    /**
     *
     */
    public function testUpdateBasketAmounts()
    {
        $this->markTestIncomplete('');

        $shopApi = $this->getShopApiWithResultFile('basket-add.json');

        $productVariantId = 123;
        $amount = 2;
        $basket = $shopApi->updateBasketAmount($this->sessionId, $productVariantId, $amount);
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
