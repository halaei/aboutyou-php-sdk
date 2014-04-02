<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;
use Collins\ShopApi\Model\Basket;

class BasketTest extends AbstractShopApiTest
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
        $shopApi = $this->getShopApiWithResultFile('result/basket1.json', $exceptedRequestBody);

        $basket = $shopApi->fetchBasket($this->sessionId);
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

        $this->assertEquals('identifier3', $items[2]->getId());
        $subItems = $items[2]->getItems();
        $this->assertEquals(300, $items[2]->getTotalPrice());
        $this->assertEquals(280, $items[2]->getTotalNet());
        $this->assertEquals(20, $items[2]->getTotalVat());
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

//        $this->assertEquals('identifier2', $items[1]->getId());
//        $this->assertTrue($items[1]->hasErrors());

        return $basket;
    }

    public function testBasketGetCollectedItems()
    {
        $exceptedRequestBody = '[{"basket":{"session_id":"testing"}}]';
        $shopApi = $this->getShopApiWithResultFile('result/basket-similar-items.json', $exceptedRequestBody);

        $basket = $shopApi->fetchBasket($this->sessionId);
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
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"id":"item1","variant_id":123}]}}]';
        $shopApi = $this->getMockedShopApiWithResultFile(array('generateBasketItemId'), 'result/basket1.json', $exceptedRequestBody);
        $shopApi->expects($this->once())
            ->method('generateBasketItemId')
            ->withAnyParameters()
            ->will($this->returnValue('item1'))
        ;
        // add one item to basket
        $basket = $shopApi->addItemToBasket($this->sessionId, 123);
        $this->checkBasket($basket);

        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"id":"item1","variant_id":123}]}}]';
        $shopApi = $this->getMockedShopApiWithResultFile(array('generateBasketItemId'), 'result/basket1.json', $exceptedRequestBody);
        $shopApi->expects($this->once())
            ->method('generateBasketItemId')
            ->withAnyParameters()
            ->will($this->returnValue('item1'))
        ;
        // add one item to basket
        $basket = $shopApi->addItemToBasket($this->sessionId, '123');
        $this->checkBasket($basket);

        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"id":"item2","variant_id":123}]}}]';
        $shopApi = $this->getMockedShopApiWithResultFile(array('generateBasketItemId'), 'result/basket1.json', $exceptedRequestBody);
        $shopApi->expects($this->once())
            ->method('generateBasketItemId')
            ->will($this->returnValue('item2'))
        ;
        // add more of one item to basket
        $basket = $shopApi->addItemToBasket($this->sessionId, 123);
        $this->checkBasket($basket);
    }

    /**
     * @expectedException \Collins\ShopApi\Exception\InvalidParameterException
     */
    public function testAddToBasketThrowsException()
    {
        $shopApi = $this->getShopApiWithResultFile('result/basket1.json');
        $variant = new ShopApi\Model\Variant(json_decode('{"id":123}'), $shopApi->getResultFactory());
        $shopApi->addItemToBasket($this->sessionId, $variant);
    }

    /**
     * @expectedException \Collins\ShopApi\Exception\InvalidParameterException
     */
    public function testAddToBasketThrowsException2()
    {
        $shopApi = $this->getShopApiWithResultFile('result/basket1.json');
        $item = new Basket\BasketItem('item_id', 123);
        $shopApi->addItemToBasket($this->sessionId, $item);
    }

    public function testRemoveFromBasket()
    {
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"delete":"item3"}]}}]';
        $shopApi = $this->getShopApiWithResultFile('result/basket1.json', $exceptedRequestBody);
        // remove all of one item from basket
        $basket = $shopApi->removeItemsFromBasket($this->sessionId, array('item3'));
        $this->checkBasket($basket);

        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"delete":"item3"},{"delete":"item4"}]}}]';
        $shopApi = $this->getShopApiWithResultFile('result/basket1.json', $exceptedRequestBody);
        // remove all of one item from basket
        $basket = $shopApi->removeItemsFromBasket($this->sessionId, array('item3', 'item4'));
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

        $basket->deleteItem('item3');
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"delete":"item3"}]}}]';
        $shopApi = $this->getShopApiWithResultFile('result/basket1.json', $exceptedRequestBody);
        $shopApi->updateBasket($this->sessionId, $basket);

        $basket = Basket::createFromJson(json_decode('{"products":[], "order_line":[], "total_price":123, "total_net":12,"total_vat":34}'), $shopApi->getResultFactory());
        $basket->updateItem(new Basket\BasketItem('item1', 123));
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"id":"item1","variant_id":123}]}}]';
        $shopApi = $this->getShopApiWithResultFile('result/basket1.json', $exceptedRequestBody);
        $shopApi->updateBasket($this->sessionId, $basket);

        $basket = new Basket();
        $basket->updateItem(new Basket\BasketItem('item2', 123));
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"id":"item2","variant_id":123}]}}]';
        $shopApi = $this->getShopApiWithResultFile('result/basket1.json', $exceptedRequestBody);
        $shopApi->updateBasket($this->sessionId, $basket);

        $basket = new Basket();
        $basket->updateItem(new Basket\BasketItem('item3', 123, array('description'=>'Wudnerschön')));
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"id":"item3","variant_id":123,"additional_data":{"description":"Wudnersch\u00f6n"}}]}}]';
        $shopApi = $this->getShopApiWithResultFile('result/basket1.json', $exceptedRequestBody);
        $shopApi->updateBasket($this->sessionId, $basket);

        $basket = new Basket();
        $item = new Basket\BasketItem('item3', 123);
        $item->setAdditionData(array('description'=>'Wudnerschön'));
        $basket->updateItem($item);
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":[{"id":"item3","variant_id":123,"additional_data":{"description":"Wudnersch\u00f6n"}}]}}]';
        $shopApi = $this->getShopApiWithResultFile('result/basket1.json', $exceptedRequestBody);
        $shopApi->updateBasket($this->sessionId, $basket);

        $updatedItem4 = <<<EOS
        {
            "id": "identifier4",
            "additional_data": {"description": "Wudnersch\u00f6n und so"},
            "set_items": [
                {
                    "variant_id": 12312121
                },
                {
                    "variant_id": 66666,
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
            array('description' => 'Wudnerschön und so')
            ));
        $exceptedRequestBody = '[{"basket":{"session_id":"testing","order_lines":['. $updatedItem4 .']}}]';
        $shopApi = $this->getShopApiWithResultFile('result/basket1.json', $exceptedRequestBody);
        $shopApi->updateBasket($this->sessionId, $basket);


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
        $this->assertInternalType('int', $item->getTotalPrice());
        $this->assertInternalType('float', $item->getTax());
        $this->assertInternalType('int', $item->getTotalNet());
        $this->assertInternalType('int', $item->getTotalVat());
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
