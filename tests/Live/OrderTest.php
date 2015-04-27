<?php

namespace AboutYou\SDK\Test\Live;

/**
 * @group live
 */
class OrderTest extends \AboutYou\SDK\Test\Live\AbstractAYLiveTest
{
    /**
     * @expectedException \AboutYou\SDK\Exception\ResultErrorException
     * @expectedExceptionMessage order_id: False is not of type 'integer'
     */
    public function testFetchOrderWithWrongId()
    {
        $ay = $this->getAY();
        $ay->fetchOrder(false);
    }
    
    /**
     * @expectedException \AboutYou\SDK\Exception\ResultErrorException
     * @expectedExceptionMessage Basket is empty: 123456xyz
     */
    public function testInitiateOrderWithEmptyBasket()
    {
        $ay = $this->getAY();
        
        $basket = $ay->fetchBasket($this->getSessionId());
        
        if ($basket->getTotalAmount() !== 0) {
            $this->fail('The basket is not empty!');
        }

        $result = $ay->initiateOrder($this->getSessionId(), 'http://google.de');
        var_dump($result);
        die;
    }
}
