<?php

namespace Collins\ShopApi\Test\Live;

use Collins\ShopApi;
use Collins\ShopApi\Model\Basket;

class BasketTest extends \Collins\ShopApi\Test\Live\AbstractShopApiLiveTest
{

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFetchBasketWithFalseSessionId()
    {
        $api = $this->getShopApi();
        $api->fetchBasket(false);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFetchBasketWithIntSessionId()
    {
        $api = $this->getShopApi();
        $api->fetchBasket(123456);      
    }    
    
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

        $basket = $api->addItemToBasket($this->getSessionId(), $this->getVariantId(1));
        $basket = $api->addItemToBasket($this->getSessionId(), $this->getVariantId(2));
        $basket = $api->addItemToBasket($this->getSessionId(), $this->getVariantId(3), 3);
        
        $set = new Basket\BasketSet("123456", ['image_url' => "http://", 'description' => 'Hallo']);
        $item = new Basket\BasketSetItem($this->getVariantId(4));
        
        $set->addItem($item);
        $basket->updateItemSet($set);
        
        $basket = $api->updateBasket($this->getSessionId(), $basket);
        
        $this->assertEquals(6, $basket->getTotalAmount());
        
        return $basket;
    }
    
    /**
     * @depends testAddProductToBasket
     */
    public function testRemoveAllProductsInBasket($basket)
    {
        $api = $this->getShopApi(); 
        $basket->deleteAllItems();
        
        $result = $api->updateBasket($this->getSessionId(), $basket);
        
        $this->assertEquals(0, $result->getTotalAmount());
    }
        
    public function testAddItemToBasketWithProductID()
    {
        $api = $this->getShopApi();        
        $basket = $api->addItemToBasket($this->getSessionId(), $this->getProductId(1));
        
        $this->assertTrue($basket->hasErrors());
    } 
    
    public function testAddItemSetToBasketWithProductID()
    {
        $shopApi = $this->getShopApi();        
        $basket = new Basket();
        
        $set = new Basket\BasketSet('A123567', ['description' => 'test', 'image_url' => 'http://img-url']);                
        $item = new Basket\BasketSetItem($this->getProductId(1));
        
        $set->addItem($item);
        
        $basket->updateItemSet($set);
        $result = $shopApi->updateBasket($this->getSessionId(), $basket);
        
        $this->assertTrue($result->hasErrors());
    }   
    
}

