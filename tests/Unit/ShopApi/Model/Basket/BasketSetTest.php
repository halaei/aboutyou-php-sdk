<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Unit\Model\Basket;

use Collins\ShopApi\Factory\DefaultModelFactory;
use Collins\ShopApi\Model\Basket\BasketSet;
use Collins\ShopApi\Test\Unit\Model\AbstractModelTest;

class BasketSetTest extends AbstractModelTest
{
    public function testConstruct()
    {
        $id = "12";                
        $basketSet  = new BasketSet($id, array('description' => 'blah', 'image_url'=> 'http://example.com/x.jpg'));         
        $this->assertInstanceOf('Collins\ShopApi\Model\Basket\BasketSet', $basketSet);
        
    }

}
 