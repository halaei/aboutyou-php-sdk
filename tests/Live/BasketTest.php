<?php

namespace Collins\ShopApi\Test\Live;

use Collins\ShopApi;
use Collins\ShopApi\Model\Basket;

class BasketTest extends \Collins\ShopApi\Test\Live\AbstractShopApiLiveTest
{
    
    /**
     * @expectedException \InvalidArgumentException
     * @group live
     */
    public function testFetchBasketWithFalseSessionId()
    {
        $api = $this->getShopApi();
        $api->fetchBasket(false);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @group live
     */
    public function testFetchBasketWithIntSessionId()
    {
        $api = $this->getShopApi();
        $api->fetchBasket(123456);      
    }    
    
    /**
     * @group live
     */
    public function testEmptyBasket()
    {        
        $this->clearBasket();
        
        $api = $this->getShopApi();

        $basket = $api->fetchBasket($this->getSessionId());    
        $amount = $basket->getTotalAmount();   
        
        $this->assertEquals(0, $amount);
    }
        
    /**
     * @group live     
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
     * @group live     
     * @depends testAddProductToBasket
     */
    public function testRemoveAllProductsInBasket($basket)
    {
        $api = $this->getShopApi(); 
        $basket->deleteAllItems();
        
        $result = $api->updateBasket($this->getSessionId(), $basket);
        
        $this->assertEquals(0, $result->getTotalAmount());
    }
    
    /**
     * @group live
     */
    public function testAddOneItemToBasket()
    {
        $api = $this->getShopApi();
        
        $item = new Basket\BasketItem('1234', $this->getVariantId(1), array('description' => 'test',
                                                                            'image_url' => 'http://www.google.de',
                                                                            'foo' => 'bar'));
        
        $basket = new Basket();
        $basket->updateItem($item);
        
        $basket = $api->updateBasket($this->getSessionId(), $basket);
        $item   = $basket->getItem('1234');
        
        $this->assertEquals(1, $basket->getTotalAmount());
        $this->assertInstanceOf('\Collins\ShopApi\Model\Basket\BasketItem', $item);
        
        $data = $item->getAdditionalData();
        
        $this->assertEquals('test', $data['description']);
        $this->assertEquals('http://www.google.de', $data['image_url']);
        $this->assertEquals('bar', $data['foo']);  
        
        $basket->deleteAllItems();
        $api->updateBasket($this->getSessionId(), $basket);
    }
    
    /**
     * @group live
     */
    public function testAddOneItemSetToBasket()
    {
        $api = $this->getShopApi();
        
        $item = new Basket\BasketSetItem($this->getVariantId(1));
        
        $set = new Basket\BasketSet('1234', array('description' => 'test',
                                                  'image_url' => 'http://www.google.de',
                                                  'foo' => 'bar'));
        $set->addItem($item);
        
        $basket = new Basket();
        
        $basket->updateItemSet($set);
        
        $basket = $api->updateBasket($this->getSessionId(), $basket);
        $set = $basket->getItem('1234');
               
        $this->assertEquals(1, $basket->getTotalAmount());
        $this->assertInstanceOf('\Collins\ShopApi\Model\Basket\BasketSet', $set);
        
        $items = $set->getItems();
        $this->assertCount(1, $items);
        
        $data = $set->getAdditionalData();
        
        $this->assertEquals('test', $data['description']);
        $this->assertEquals('http://www.google.de', $data['image_url']);
        $this->assertEquals('bar', $data['foo']); 
        
        $basket->deleteAllItems();
        $api->updateBasket($this->getSessionId(), $basket);        
    }
    
    /**
     * @group live
     */
    public function testAddItemToBasketWithProductID()
    {
        $api = $this->getShopApi();        
        $basket = $api->addItemToBasket($this->getSessionId(), $this->getProductId(1));
        
        $this->assertTrue($basket->hasErrors());
    } 
    
    /**
     * @group live
     */
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
    
    private function clearBasket()
    {
        $api = $this->getShopApi();       
        $basket = $api->fetchBasket($this->getSessionId());    

        $basket->deleteAllItems();
        $basket = $api->updateBasket($this->getSessionId(), $basket);       
    }    
}

