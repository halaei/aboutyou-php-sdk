<?php

namespace Collins\ShopApi\Test\Live;


class OrderTest extends \Collins\ShopApi\Test\Live\AbstractShopApiLiveTest
{
    /**
     * @expectedException \Collins\ShopApi\Exception\ResultErrorException
     * @expectedExceptionMessage order_id: False is not of type 'integer'
     * @group live
     */
    public function testFetchOrderWithWrongId()
    {
        $shopApi = $this->getShopApi();        
        $shopApi->fetchOrder(false);
    }
    
    /**
     * @expectedException \Collins\ShopApi\Exception\ResultErrorException
     * @expectedExceptionMessage Basket is empty: 123456xyz
     * @group live
     */
    public function testInitiateOrderWithEmptyBasket()
    {
        $shopApi = $this->getShopApi();
        
        $basket = $shopApi->fetchBasket($this->getSessionId());
        
        if ($basket->getTotalAmount() !== 0) {
            $this->fail('The basket is not empty!');
        }

        $shopApi->initiateOrder($this->getSessionId(), 'http://google.de');
    }
}
