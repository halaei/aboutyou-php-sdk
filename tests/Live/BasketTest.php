<?php

namespace Collins\ShopApi\Test\Live;

use Collins\ShopApi;
use Collins\ShopApi\Model\Basket;

class BasketTest extends \Collins\ShopApi\Test\Live\AbstractShopApiLiveTest
{

    public function testEmptyBasket()
    {
        $api = $this->getShopApi();
          
        $basket = $api->fetchBasket($this->getSessionId());
        $amount = $basket->getTotalAmount();
        
        $this->assertEquals(0, $amount);
    }
        
    /**
     * @depends testEmptyBasket
     */
    public function testAddProductToBasket()
    {
        $api = $this->getShopApi(); 
        
        $basket = $api->addItemToBasket($this->getSessionId(), $this->getVariantId());        
        
        $this->assertEquals(1, $basket->getTotalAmount());
    }
    
    /**
     * @depends testAddProductToBasket
     */
    public function testRemoveAllProductsInBasket()
    {
        $api = $this->getShopApi(); 
        $basket = $api->fetchBasket($this->getSessionId());
        $basket->deleteAllItems();
        
        $result = $api->updateBasket($this->getSessionId(), $basket);
        
        $this->assertEquals(0, $result->getTotalAmount());
    }
        
    public function testAddItemToBasketWithProductID()
    {
        $api = $this->getShopApi();        
        $basket = $api->addItemToBasket($this->getSessionId(), $this->getProductId());
        
        $this->assertTrue($basket->hasErrors());
    } 
    
    public function testAddItemSetToBasketWithProductID()
    {
        $shopApi = $this->getShopApi();        
        $basket = new Basket();
        
        $set = new Basket\BasketSet('A123567', ['description' => 'test', 'image_url' => 'http://img-url']);                
        $item = new Basket\BasketSetItem($this->getProductId());
        $set->addItem($item);
        
        $basket->updateItemSet($set);
        $result = $shopApi->updateBasket($this->getSessionId(), $basket);
        
        $this->assertTrue($result->hasErrors());
    }    
    
}

