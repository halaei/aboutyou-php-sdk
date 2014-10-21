<?php

namespace AboutYou\SDK\Test\Live;

/**
 * @group live
 */
class OrderTest extends \AboutYou\SDK\Test\Live\AbstractShopApiLiveTest
{
    /**
     * @expectedException \AboutYou\SDK\Exception\ResultErrorException
     * @expectedExceptionMessage order_id: False is not of type 'integer'
     */
    public function testFetchOrderWithWrongId()
    {
        $shopApi = $this->getShopApi();        
        $shopApi->fetchOrder(false);
    }
    
    /**
     * @expectedException \AboutYou\SDK\Exception\ResultErrorException
     * @expectedExceptionMessage Basket is empty: 123456xyz
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
