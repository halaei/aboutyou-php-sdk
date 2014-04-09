<?php

namespace Collins\ShopApi\Test\Live;


class OrderTest extends \Collins\ShopApi\Test\Live\AbstractShopApiLiveTest
{
    /**
     * @expectedException \Collins\ShopApi\Exception\ResultErrorException
     */
    public function testFetchOrderWithWrongId()
    {
        $shopApi = $this->getShopApi();        
        $order = $shopApi->fetchOrder(false);
    }
    
    public function testInitiateOrderWithEmptyBasket()
    {
        $shopApi = $this->getShopApi();
        
        $basket = $shopApi->fetchBasket($this->getSessionId());
        
        if ($basket->getTotalAmount() == 0) {
            $result = $shopApi->initiateOrder($this->getSessionId(), 'http://google.de');
            $this->assertEquals(400, $result->getErrorCode());
        } else {
            $this->fail('The basket isnt empty!');            
        }              
    }
    
    
}