<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

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
     *
     */
    public function testBasket()
    {
        $shopApi = $this->getShopApiWithResultFile('basket.json');

        $basket = $shopApi->fetchBasket($this->sessionId);
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

        return $basket;
    }

    /**
     *
     */
    public function testAddToBasket()
    {
        $this->markTestIncomplete();

        // add one item to basket
        $productVariantId = 123;
        $success = $this->api->addToBasket($this->sessionId, $productVariantId);
        $this->assertTrue($success);

        // add more of one item to basket
        $productVariantId = 123;
        $quantity = 2;
        $success = $this->api->addToBasket($this->sessionId, $productVariantId, $quantity);
        $this->assertTrue($success);
    }

    /**
     *
     */
    public function testRemoveFromBasket()
    {
        $this->markTestIncomplete();

        // remove one item from basket
        $productVariantId = 123;
        $success = $this->api->removeFromBasket($this->sessionId, $productVariantId);
        $this->assertTrue($success);

        // remove more of one item from basket
        $productVariantId = 123;
        $quantity = 2;
        $success = $this->api->removeFromBasket($this->sessionId, $productVariantId, $quantity);
        $this->assertTrue($success);
    }
}
