<?php

namespace AboutYou\SDK\Test\Live;

use \AY;
use AboutYou\SDK\Model\WishList;


/**
 * @group live
 */
class WishListTest extends \AboutYou\SDK\Test\Live\AbstractAYLiveTest
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFetchWishListWithFalseSessionId()
    {
        $api = $this->getAY();
        $api->fetchWishList(false);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFetchWishListWithIntSessionId()
    {
        $api = $this->getAY();
        $api->fetchWishList(123456);      
    }    
    
    public function testEmptyWishList()
    {        
        $this->clearWishList();
        
        $api = $this->getAY();
        $WishList = $api->fetchWishList($this->getSessionId());    
        $amount = $WishList->getTotalAmount();   
              
        $this->assertEquals(0, $amount);
    }
        
    /**
     * @depends testEmptyWishList
     */
    public function testAddProductToWishList()
    {        
        $api = $this->getAY();
   
        $WishList = $api->addItemToWishList($this->getSessionId(), $this->getVariantId(1));

        $WishList = $api->addItemToWishList($this->getSessionId(), $this->getVariantId(3));

        $WishList = $api->addItemToWishList($this->getSessionId(), $this->getVariantId(6), 3);

        $set = new WishList\WishListSet("123456", array('image_url' => "http://", 'description' => 'Hallo'));
        $item = new WishList\WishListSetItem($this->getVariantId(7), array());
        $item2 = new WishList\WishListSetItem($this->getVariantId(8), array());

        $set->addItem($item);

        $set->addItem($item2);
        $WishList->updateItemSet($set);
               
        $WishList = $api->updateWishList($this->getSessionId(), $WishList);
        
        $set = $WishList->getItem("123456");
        $items = $set->getItems();
        $item = $items[0];  
        $this->assertEquals(null, $item->getAppId()); 
        
        $item2 = $items[1];
        $this->assertEquals(null, $item2->getAppId()); 
        
        $errorCount = count($WishList->getErrors());

        $this->assertEquals(6, $WishList->getTotalAmount() + $errorCount);

        return $WishList;
    }

    /**
     * @depends testAddProductToWishList
     */
    public function testRemoveAllProductsInWishList($WishList)
    {
        $api = $this->getAY();
        $WishList->deleteAllItems();

        $WishList = $api->updateWishList($this->getSessionId(), $WishList);

        $this->assertEquals(0, $WishList->getTotalAmount());
    }

    /**
     * @depends testRemoveAllProductsInWishList
     */
    public function testAddItemWithVariantNotFound()
    {
        $api = $this->getAY();

        $WishList = $api->addItemToWishList($this->getSessionId(), 1);

        $this->assertEquals(0, $WishList->getTotalAmount());
        $this->assertTrue($WishList->hasErrors());
        $this->assertCount(0, $WishList->getProducts());
    }

    public function testAddItemToWishList()
    {
        $api = $this->getAY();
        
        $item = new WishList\WishListItem(null,
            $this->getVariantId(1)
        );
        
        $WishList = new WishList();
        
        $WishList->deleteAllItems();
        $WishList->updateItem($item);
        
        $WishList = $api->updateWishList($this->getSessionId(), $WishList);
        $items = $WishList->getItems();
        $item   = reset($items);

        $this->assertInternalType('string', $item->getId());
        $this->assertGreaterThan(10, strlen($item->getId()), 'The item id ' . $item->getId() . ' is to short');
        $this->assertEquals(1, $WishList->getTotalAmount());
        $this->assertEquals(null, $item->getAppId());
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\WishList\\WishListItem', $item);
    }
    
    public function testAddItemSetToWishList()
    {
        $api = $this->getAY();

        $set = new WishList\WishListSet(null, array(
            'description' => 'test',
            'image_url' => 'http://www.google.de'
        ));
        $set->addItem(new WishList\WishListSetItem($this->getVariantId(1)));

        $WishList = new WishList();

        $WishList->deleteAllItems();
        $WishList->updateItemSet($set);

        $WishList = $api->updateWishList($this->getSessionId(), $WishList);
        $items = $WishList->getItems();
        $item   = reset($items);

        $this->assertInternalType('string', $item->getId());
        $this->assertGreaterThan(10, strlen($item->getId()), 'The item id ' . $item->getId() . ' is to short');
        $this->assertEquals(1, $WishList->getTotalAmount());
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\WishList\\WishListSet', $item);
    }

    public function testAddOneItemToWishList()
    {
        $api = $this->getAY();

        $item = new WishList\WishListItem('1234',
            $this->getVariantId(1),
            array(
                'description' => 'test',
                'image_url' => 'http://www.google.de',
                'foo' => 'bar'
            )
        );

        $WishList = new WishList();

        $WishList->deleteAllItems();
        $WishList = $api->updateWishList($this->getSessionId(), $WishList);

        $WishList->updateItem($item);

        $WishList = $api->updateWishList($this->getSessionId(), $WishList);
        $item   = $WishList->getItem('1234');

        $this->assertEquals(1, $WishList->getTotalAmount());
        $this->assertEquals(null, $item->getAppId());
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\WishList\\WishListItem', $item);

        $data = $item->getAdditionalData();

        $this->assertEquals('test', $data['description']);
        $this->assertEquals('http://www.google.de', $data['image_url']);
        $this->assertEquals('bar', $data['foo']);

        $WishList->deleteAllItems();
        $api->updateWishList($this->getSessionId(), $WishList);
    }

    public function testAddOneItemToWishListWithAppId()
    {
        $api = $this->getAY();
        
        $item = new WishList\WishListItem('1234', 
            $this->getVariantId(1), 
            array(
                'description' => 'test',
                'image_url' => 'http://www.google.de',
                'foo' => 'bar'
            ),
            200
        );
           
        $WishList = new WishList();
        $WishList->updateItem($item);
        
        $WishList = $api->updateWishList($this->getSessionId(), $WishList);
        $item   = $WishList->getItem('1234');
                
        $this->assertEquals(1, $WishList->getTotalAmount());
        $this->assertEquals(200, $item->getAppId());
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\WishList\\WishListItem', $item);
        
        $data = $item->getAdditionalData();
        
        $this->assertEquals('test', $data['description']);
        $this->assertEquals('http://www.google.de', $data['image_url']);
        $this->assertEquals('bar', $data['foo']);  
        
        $WishList->deleteAllItems();
        $api->updateWishList($this->getSessionId(), $WishList);        
    }
    
    public function testAddOneItemSetToWishList()
    {
        $api = $this->getAY();
        
        $item = new WishList\WishListSetItem($this->getVariantId(1));
        
        $set = new WishList\WishListSet('1234', array('description' => 'test',
                                                  'image_url' => 'http://www.google.de',
                                                  'foo' => 'bar'));
        $set->addItem($item);
        
        $WishList = new WishList();
        
        $WishList->updateItemSet($set);
        
        $WishList = $api->updateWishList($this->getSessionId(), $WishList);
        $set = $WishList->getItem('1234');
               
        $this->assertEquals(1, $WishList->getTotalAmount());
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\WishList\\WishListSet', $set);
        
        $items = $set->getItems();
        $this->assertCount(1, $items);
        
        $item = $items[0];
        $this->assertNull($item->getAppId());
        
        $data = $set->getAdditionalData();
        
        $this->assertEquals('test', $data['description']);
        $this->assertEquals('http://www.google.de', $data['image_url']);
        $this->assertEquals('bar', $data['foo']); 
        
        $WishList->deleteAllItems();
        $api->updateWishList($this->getSessionId(), $WishList);        
    }
    
    public function testAddSetWithTwoItemsToWishList()
    {
        $api = $this->getAY();

        $item1 = new WishList\WishListSetItem($this->getVariantId(1), array('description' => 'Variante 1', 'hello' => 'world'));
        $item2 = new WishList\WishListSetItem($this->getVariantId(3), array('description' => 'Variante 2', 'hello' => 'universe'));

        $set = new WishList\WishListSet('set1', array(
            'description' => 'Product-Set',
            'image_url' => 'http://cdn.mary-paul.de/file/e40b90464ab4df830f6f2d5eccb0447f',
            'hello' => 'multiverse')
        );
        $set->addItem($item1);
        $set->addItem($item2);

        $WishList = new WishList();

        $WishList->updateItemSet($set);

        $WishList = $api->updateWishList($this->getSessionId(), $WishList);
        $set = $WishList->getItem('set1');

        $this->assertEquals(1, $WishList->getTotalAmount());
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\WishList\\WishListSet', $set);

        $items = $set->getItems();
        $this->assertCount(2, $items);

        $data = $set->getAdditionalData();

        $this->assertEquals('Product-Set', $data['description']);
        $this->assertEquals('http://cdn.mary-paul.de/file/e40b90464ab4df830f6f2d5eccb0447f', $data['image_url']);
        $this->assertEquals('multiverse', $data['hello']);

        $WishList->deleteAllItems();
        $api->updateWishList($this->getSessionId(), $WishList);
    }
    
    public function testAddSetWithTwoItemsWithAppIdToWishList()
    {
        $api = $this->getAY();

        $item1 = new WishList\WishListSetItem($this->getVariantId(1), array('description' => 'Variante 1', 'hello' => 'world'), 139);
        $item2 = new WishList\WishListSetItem($this->getVariantId(3), array('description' => 'Variante 2', 'hello' => 'universe'), 139);

        $set = new WishList\WishListSet('set1', array(
            'description' => 'Product-Set',
            'image_url' => 'http://cdn.mary-paul.de/file/e40b90464ab4df830f6f2d5eccb0447f',
            'hello' => 'multiverse')
        );
        
        $set->addItem($item1);
        $set->addItem($item2);

        $WishList = new WishList();

        $WishList->updateItemSet($set);
        $WishList = $api->updateWishList($this->getSessionId(), $WishList);
        
        $set = $WishList->getItem('set1');
        $items = $set->getItems();

        foreach ($items as $item) {
            $this->assertEquals(139, $item->getAppId());
        }

        $WishList->deleteAllItems();
        $api->updateWishList($this->getSessionId(), $WishList);
    }    

    public function testAddItemToWishListWithProductID()
    {
        $api = $this->getAY();
        $WishList = $api->addItemToWishList($this->getSessionId(), $this->getProductId(1));
        
        $this->assertTrue($WishList->hasErrors());
    } 
    
    public function testAddItemSetToWishListWithProductID()
    {
        $ay = $this->getAY();
        $WishList = new WishList();
        
        $set = new WishList\WishListSet('A123567', array('description' => 'test', 'image_url' => 'http://img-url'));
        $item = new WishList\WishListSetItem($this->getProductId(1));
        
        $set->addItem($item);
        
        $WishList->updateItemSet($set);
        $result = $ay->updateWishList($this->getSessionId(), $WishList);
        
        $this->assertTrue($result->hasErrors());
    }   
    
    private function clearWishList()
    {
        $api = $this->getAY();
        $WishList = $api->fetchWishList($this->getSessionId());    

        $WishList->deleteAllItems();
        $WishList = $api->updateWishList($this->getSessionId(), $WishList);


        $this->assertEquals(count($WishList->getItems()), 0);
    }


    public function testPackageId()
    {
        $api = $this->getAY();

        $sessionId = uniqid();

        $WishList = $api->addItemToWishList($sessionId, 7361626);

        $items = $WishList->getItems();

        $this->assertEquals(count($items), 1);

        $item = array_shift($items);

        $this->assertEquals($item->getPackageId(), 1);
    }

}
