<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Functional;

use AboutYou\SDK\Model\Variant;
use \AY;
use AboutYou\SDK\Model\Basket;

class BasketTest extends AbstractAYTest
{
    private $sessionId = null;

    /**
     *
     */
    public function setUp()
    {
        $this->sessionId = 'testing';
    }

    public function testBasket()
    {
        $exceptedRequestBody = '[{"basket":{"session_id":"testing"}}]';
        $ay = $this->getAYWithResultFile('result/basket1.json', $exceptedRequestBody);

        $basket = $ay->fetchBasket($this->sessionId);
        $this->checkBasket($basket);
        $this->assertTrue($basket->hasErrors());

        $items = $basket->getItems();
        $this->assertCount(2, $items);

        $this->assertEquals('identifier1', $items[0]->getId());
        $this->assertFalse($items[0]->hasErrors());
        $this->assertEquals(19.0, $items[0]->getTax());
        $this->assertEquals(400, $items[0]->getTotalPrice());
        $this->assertEquals(390, $items[0]->getTotalNet());
        $this->assertEquals(10, $items[0]->getTotalVat());
        $this->assertEquals(123, $items[0]->getProduct()->getId());
        $this->assertEquals(1543435, $items[0]->getVariant()->getId());
        $this->assertNull($items[0]->getAdditionalData());
        $this->assertNull($items[0]->getDescription());
        $this->assertEquals($items[0], $basket->getItem($items[0]->getId()));
        $this->assertEquals($items[1], $basket->getItem($items[1]->getId()));

        $this->assertEquals('identifier3', $items[1]->getId());
        $subItems = $items[1]->getItems();
        $this->assertEquals(300, $items[1]->getTotalPrice());
        $this->assertEquals(280, $items[1]->getTotalNet());
        $this->assertEquals(20, $items[1]->getTotalVat());
        $this->assertFalse($subItems[0]->hasErrors());
        $this->assertEquals(19.0, $subItems[0]->getTax());
        $this->assertEquals(600, $subItems[0]->getTotalPrice());
        $this->assertEquals(590, $subItems[0]->getTotalNet());
        $this->assertEquals(10, $subItems[0]->getTotalVat());
        $this->assertEquals(123, $subItems[0]->getProduct()->getId());
        $this->assertEquals(12312121, $subItems[0]->getVariant()->getId());
        $this->assertNotNull($subItems[0]->getAdditionalData());
        $this->assertEquals('engravingssens', $subItems[0]->getDescription());
        $this->assertEquals(array('description' => 'engravingssens', 'internal_infos' => array('stuff')), $subItems[0]->getAdditionalData());

        return $basket;
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddEmptyItemSetToBasket()
    {
        $basket = new Basket();        
        $set = new Basket\BasketSet('123', array('description' => 'test', 'image_url' => 'http://img-url'));
        $basket->updateItemSet($set);        
    }
    
    public function testAddItemToBasketWithProductID()
    {
        $ay = $this->getAYWithResultFile('basket-variant-not-found.json');
        
        $basket = $ay->addItemToBasket('123456xyz', 226651);
        $this->assertTrue($basket->hasErrors());

        $errors = $basket->getErrors();
        $error = $errors[0];
        
        $this->assertEquals('variant not found', $error->getErrorMessage());
    }
    
    public function testAddItemSetToBasketWithProductID()
    {
        $ay = $this->getAYWithResultFile('basket-set-variant-not-found.json');
        $basket = new Basket();
        
        $set = new Basket\BasketSet('A123567', array('description' => 'test', 'image_url' => 'http://img-url'));
        $item = new Basket\BasketSetItem(226651);
        $set->addItem($item);
        
        $basket->updateItemSet($set);
        $result = $ay->updateBasket('123456xyz', $basket);
         
        $this->assertTrue($result->hasErrors());
    }

    public function testAddItemSetToBasketAndSingleItemFailed()
    {
        $ay = $this->getAYWithResultFile('basket-set-with-failed-item.json');
        $basket = new Basket();

        $set = new Basket\BasketSet('A123567', array('description' => 'test', 'image_url' => 'http://img-url'));
        $set->addItem(new Basket\BasketSetItem(226651));

        $basket->updateItemSet($set);
        $result = $ay->updateBasket('123456xyz', $basket);

        $this->assertTrue($result->hasErrors());
    }

    /**
     * @expectedException \AboutYou\SDK\Exception\ResultErrorException
     */
    public function testAddItemSetToBasketWithWrongBasketSetID()
    {
        $ay = $this->getAYWithResultFile('basket-set-with-int-id.json');
        $basket = new Basket();
        
        $set = new Basket\BasketSet('WRONG_ID', array('description' => 'test', 'image_url' => 'http://img-url'));
        $item = new Basket\BasketSetItem(226651);
        $set->addItem($item);
        
        $basket->updateItemSet($set);
        
        $ay->updateBasket('123456xyz', $basket);
    }

    /**
     * @expectedException \AboutYou\SDK\Exception\UnexpectedResultException
     */
    public function testAddItemToBasketWithWrongProductsResult()
    {
        $ay = $this->getAYWithResultFile('basket-without-product.json');
        $ay->addItemToBasket('123456xyz', 1543435);
    }
    
    public function testAddItemToBasketWithWrongProductsResultInSet()
    {
        $ay = $this->getAYWithResultFile('basket-set-without-product.json');
        $basket = new Basket();
        
        $set = new Basket\BasketSet('123', array('description' => 'test', 'image_url' => 'http://img-url'));
        $item = new Basket\BasketSetItem(12312121);
        $set->addItem($item);
        
        $basket->updateItemSet($set);
        $result = $ay->updateBasket('123456xyz', $basket);

        $this->assertTrue($result->hasErrors());
    }
    
   

    public function testBasketGetCollectedItems()
    {
        $exceptedRequestBody = '[{"basket":{"session_id":"testing"}}]';
        $ay = $this->getAYWithResultFile('result/basket-similar-items.json', $exceptedRequestBody);

        $basket = $ay->fetchBasket($this->sessionId);
        $this->checkBasket($basket);
        $this->assertFalse($basket->hasErrors());

        $items = $basket->getItems();
        $this->assertCount(5, $items);

        $items = $basket->getCollectedItems();
        $this->assertCount(3, $items);

        $this->assertInternalType('array', $items[0]);
        $this->assertEquals(2, $items[0]['amount']);
        $this->assertEquals(800, $items[0]['price']);

        $this->assertInternalType('array', $items[1]);
        $this->assertEquals(2, $items[1]['amount']);
        $this->assertEquals(800, $items[1]['price']);

        $this->assertInternalType('array', $items[2]);
        $this->assertEquals(1, $items[2]['amount']);
        $this->assertEquals(400, $items[2]['price']);
    }

    public function testAddToBasket()
    {
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"id":"item1","variant_id":123,"app_id":null}]}}]';
        $ay = $this->getMockedAYWithResultFile(array('generateBasketItemId'), 'result/basket1.json', $exceptedRequestBody);
        $ay->expects($this->once())
            ->method('generateBasketItemId')
            ->withAnyParameters()
            ->will($this->returnValue('item1'))
        ;
        // add one item to basket
        $basket = $ay->addItemToBasket($this->sessionId, 123);
        $this->checkBasket($basket);

        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"id":"item1","variant_id":123,"app_id":null}]}}]';
        $ay = $this->getMockedAYWithResultFile(array('generateBasketItemId'), 'result/basket1.json', $exceptedRequestBody);
        $ay->expects($this->once())
            ->method('generateBasketItemId')
            ->withAnyParameters()
            ->will($this->returnValue('item1'))
        ;
        // add one item to basket
        $basket = $ay->addItemToBasket($this->sessionId, '123');
        $this->checkBasket($basket);

        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"id":"item2","variant_id":123,"app_id":null}]}}]';
        $ay = $this->getMockedAYWithResultFile(array('generateBasketItemId'), 'result/basket1.json', $exceptedRequestBody);
        $ay->expects($this->once())
            ->method('generateBasketItemId')
            ->will($this->returnValue('item2'))
        ;
        // add more of one item to basket
        $basket = $ay->addItemToBasket($this->sessionId, 123);
        $this->checkBasket($basket);
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */    
    public function testAddToItemsToSetWithDifferentAppIds()
    {
        $basketItemSet = new Basket\BasketSet('123', array('image_url' => 'www', 'description' => 'Test'));
        
        $item = new Basket\BasketSetItem(1234, array(), 139);
        $item2 = new Basket\BasketSetItem(1234, array(), 200);
        
        $basketItemSet->addItem($item);
        $basketItemSet->addItem($item2);
    }
    
    public function testAddTwoItemsToSetWithAppIds()
    {
        $basketItemSet = new Basket\BasketSet('123', array('image_url' => 'www', 'description' => 'Test'));
        
        $item = new Basket\BasketSetItem(1234, array(), 139);
        $item2 = new Basket\BasketSetItem(1234, array(), 139);
        
        $basketItemSet->addItem($item);
        $basketItemSet->addItem($item2);
        
        foreach ($basketItemSet->getItems() as $item) {
            $this->assertEquals(139, $item->getAppId());
        }
    }  
    
    public function testAddTwoItemsToSetWithoutAppIds()
    {
        $basketItemSet = new Basket\BasketSet('123', array('image_url' => 'www', 'description' => 'Test'));
        
        $item = new Basket\BasketSetItem(1234, array());
        $item2 = new Basket\BasketSetItem(1234, array());
        
        $basketItemSet->addItem($item);
        $basketItemSet->addItem($item2);
        
        foreach ($basketItemSet->getItems() as $item) {
            $this->assertEquals(null, $item->getAppId());
        }
    } 
    
    public function testAddItemToBasket()
    {
        $item = new Basket\BasketItem("123", 1234, [], 200);
        
        $this->assertEquals(200, $item->getAppId());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddToBasketThrowsException()
    {
        $ay = $this->getAYWithResultFile('result/basket1.json');
        $variant = Variant::createFromJson(json_decode('{"id":123}'), $ay->getResultFactory(), $this->getProduct());
        $ay->addItemToBasket($this->sessionId, $variant);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddToBasketThrowsException2()
    {
        $ay = $this->getAYWithResultFile('result/basket1.json');
        $item = new Basket\BasketItem('item_id', 123);
        $ay->addItemToBasket($this->sessionId, $item);
    }

    public function testRemoveFromBasket()
    {
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"delete":"item3"}]}}]';
        $ay = $this->getAYWithResultFile('result/basket1.json', $exceptedRequestBody);
        // remove all of one item from basket
        $basket = $ay->removeItemsFromBasket($this->sessionId, array('item3'));
        $this->checkBasket($basket);

        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"delete":"item3"},{"delete":"item4"}]}}]';
        $ay = $this->getAYWithResultFile('result/basket1.json', $exceptedRequestBody);
        // remove all of one item from basket
        $basket = $ay->removeItemsFromBasket($this->sessionId, array('item3', 'item4'));
        $this->checkBasket($basket);
    }
     
    public function testAddAdditionalDataToBasketItemWithDescription()
    {
        $basketItem = new Basket\BasketItem('item_id', 123);
        $basketItem->setAdditionData(array('description' => 'test')); 
        
        $this->assertEquals('test', $basketItem->getDescription());
    }
      
    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddAdditionalDataToBasketItemWithoutDescription()
    {
        $basketItem = new Basket\BasketItem('item_id', 123);
        $basketItem->setAdditionData(array('foo' => 'bar')); 
    }
    
    /**
     * @expectedException InvalidArgumentException
     */    
    public function testAddEmptyAdditionalDataToBasketSet()
    {   
        $basketItemSet = new Basket\BasketSet('123', array());        
    }  
    
    /**
     * @expectedException InvalidArgumentException
     */      
    public function testAddOnlyImageAdditionalDataToBasketSet()
    {  
        $basketItemSet = new Basket\BasketSet('123', array('image_url' => 'www'));        
    }  
    
    /**
     * @expectedException InvalidArgumentException
     */      
    public function testAddOnlyDescAdditionalDataToBasketSet()
    {    
        $basketItemSet = new Basket\BasketSet('123', array('description' => 'www'));  
        
    }   
    
    public function testAddAdditionalDataToBasketSet()
    {        
        $basketItemSet = new Basket\BasketSet('123', array('image_url' => 'www', 'description' => 'Test'));
        
        $this->assertEquals('Test', $basketItemSet->getDescription());
        $this->assertCount(2, $basketItemSet->getAdditionalData());
    }    
    
    /**
     * @expectedException InvalidArgumentException
     */     
    public function testCreateBasketItemWithWrongId()
    {
        $item = new Basket\BasketItem(123, 12345);
    }
    
    /**
     * @depends testBasket
     */
    public function testUpdateBasket(Basket $basket)
    {
        $exceptedRequestBody = '[{"basket":{"session_id":"testing"}}]';

        $ay = $this->getAYWithResultFile('result/basket1.json', $exceptedRequestBody);

        $basket = $ay->updateBasket($this->sessionId, $basket);
        $this->checkBasket($basket);

        $basket->deleteItem('item3');
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"delete":"item3"}]}}]';
        $ay = $this->getAYWithResultFile('result/basket1.json', $exceptedRequestBody);
        $ay->updateBasket($this->sessionId, $basket);

        $basket = Basket::createFromJson(json_decode('{"products":[], "order_line":[], "total_price":123, "total_net":12,"total_vat":34}'), $ay->getResultFactory());
        $basket->updateItem(new Basket\BasketItem('item1', 123));
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"id":"item1","variant_id":123,"app_id":null}]}}]';
        $ay = $this->getAYWithResultFile('result/basket1.json', $exceptedRequestBody);
        $ay->updateBasket($this->sessionId, $basket);

        $basket = new Basket();
        $basket->updateItem(new Basket\BasketItem('item2', 123));
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"id":"item2","variant_id":123,"app_id":null}]}}]';
        $ay = $this->getAYWithResultFile('result/basket1.json', $exceptedRequestBody);
        $ay->updateBasket($this->sessionId, $basket);

        $basket = new Basket();
        $basket->updateItem(new Basket\BasketItem('item3', 123, array('description'=>'Wudnerschön')));
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"id":"item3","variant_id":123,"app_id":null,"additional_data":{"description":"Wudnersch\u00f6n"}}]}}]';
        $ay = $this->getAYWithResultFile('result/basket1.json', $exceptedRequestBody);
        $ay->updateBasket($this->sessionId, $basket);

        $basket = new Basket();
        $item = new Basket\BasketItem('item3', 123);
        $item->setAdditionData(array('description'=>'Wudnerschön'));
        $basket->updateItem($item);
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"id":"item3","variant_id":123,"app_id":null,"additional_data":{"description":"Wudnersch\u00f6n"}}]}}]';
        $ay = $this->getAYWithResultFile('result/basket1.json', $exceptedRequestBody);
        $ay->updateBasket($this->sessionId, $basket);

        $updatedItem4 = <<<EOS
        {
            "id": "identifier4",
            "additional_data": {"description": "Wudnersch\u00f6n und so", "image_url": "http://google.de"},
            "set_items": [
                {
                    "variant_id": 12312121,
                    "app_id":null
                },
                {
                    "variant_id": 66666,
                    "app_id":null,
                    "additional_data": {
                        "description": "engravingssens",
                        "internal_infos":["stuff"]
                    }
                }
            ]
        }
EOS;
        $updatedItem4 = json_encode(json_decode($updatedItem4)); // reformat

        $basket = new Basket();
        $basket->updateItemSet(Basket\BasketSet::create(
            'identifier4',
            array(
                array(12312121),
                array(66666, array('description' => 'engravingssens', 'internal_infos' => array('stuff')))
            ),
            array('description' => 'Wudnerschön und so', "image_url" => "http://google.de")
            ));
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":['. $updatedItem4 .']}}]';
        $ay = $this->getAYWithResultFile('result/basket1.json', $exceptedRequestBody);
        $ay->updateBasket($this->sessionId, $basket);


    }

    /**
     * Check if given object is a valid basket.
     */
    private function checkBasket(Basket $basket)
    {
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Basket', $basket);
        $this->assertInternalType('int', $basket->getTotalPrice());
        $this->assertInternalType('int', $basket->getTotalNet());
        $this->assertInternalType('int', $basket->getTotalVat());
        $this->assertInternalType('int', $basket->getTotalAmount());
        $this->assertInternalType('int', $basket->getTotalVariants());

        foreach ($basket->getItems() as $item) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Basket\BasketItemInterface', $item);
            if ($item instanceof Basket\BasketItem) {
                $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Basket\BasketItem', $item);
                $this->checkBasketVariantItem($item);
            } else {
                $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Basket\BasketSet', $item);
                $this->checkBasketSet($item);
            }
        }
    }

    private function checkBasketVariantItem(Basket\BasketVariantItem $item)
    {
        $this->assertInternalType('int', $item->getTotalPrice());
        $this->assertInternalType('float', $item->getTax());
        $this->assertInternalType('int', $item->getTotalNet());
        $this->assertInternalType('int', $item->getTotalVat());
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Product', $item->getProduct());
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Variant', $item->getVariant());

    }

    private function checkBasketSet(Basket\BasketSet $set)
    {
        foreach ($set->getItems() as $item) {
            $this->checkBasketVariantItem($item);
        }
    }
    
    private function getProduct() 
    {       
        $productIds = array(123, 456);

        $ay = $this->getAYWithResultFile('result/products.json');

        $productResult = $ay->fetchProductsByIds($productIds);
        $products = $productResult->getProducts();

        
        return $products[123];        
    }
}
