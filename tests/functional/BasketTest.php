<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;
use Collins\ShopApi\Model\Basket;

class BasketTest extends ShopApiTest
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
     * Check if given object is a valid basket.
     */
    private function checkBasket(Basket $basket)
    {
        $this->assertInstanceOf('Collins\ShopApi\Model\Basket', $basket);
        $this->assertInternalType('int', $basket->getTotalPrice());
        $this->assertInternalType('int', $basket->getTotalNet());
        $this->assertInternalType('int', $basket->getTotalVat());
        $this->assertInternalType('int', $basket->getTotalQuantity());
        $this->assertInternalType('int', $basket->getTotalVariants());

        foreach ($basket->getItems() as $item) {
            $this->assertInstanceOf('Collins\ShopApi\Model\BasketItem', $item);
            $this->assertInternalType('int', $item->getTotalPrice());
            $this->assertInternalType('int', $item->getUnitPrice());
            $this->assertInternalType('int', $item->getQuantity());
            $this->assertInternalType('int', $item->getTax());
            $this->assertInternalType('int', $item->getVat());
            $this->assertInstanceOf('Collins\ShopApi\Model\Product', $item->getProduct());
            $this->assertInstanceOf('Collins\ShopApi\Model\ProductVariant', $item->getProductVariant());
        }
    }

    /**
     *
     */
    public function testBasket()
    {
        $shopApi = $this->getShopApiWithResultFile('basket.json');

        $basket = $shopApi->fetchBasket($this->sessionId);
        $this->checkBasket($basket);
    }

    /**
     *
     */
    public function testAddToBasket()
    {
        $shopApi = $this->getShopApiWithResultFile('basket-add.json');

        // add one item to basket
        $productVariantId = 123;
        $basket = $shopApi->addToBasket($this->sessionId, $productVariantId);
        $this->checkBasket($basket);

        // add more of one item to basket
        $productVariantId = 123;
        $amount = 2;
        $basket = $shopApi->addToBasket($this->sessionId, $productVariantId, $amount);
        $this->checkBasket($basket);
    }

    /**
     *
     */
    public function testRemoveFromBasket()
    {
        $shopApi = $this->getShopApiWithResultFile('basket-add.json');

        // remove all of one item from basket
        $productVariantId = 123;
        $basket = $shopApi->removeFromBasket($this->sessionId, $productVariantId);
        $this->checkBasket($basket);
    }

    /**
     *
     */
    public function testUpdateBasketAmounts()
    {
        $shopApi = $this->getShopApiWithResultFile('basket-add.json');

        $productVariantId = 123;
        $amount = 2;
        $basket = $shopApi->updateBasketAmount($this->sessionId, $productVariantId, $amount);
        $this->checkBasket($basket);
    }
}
