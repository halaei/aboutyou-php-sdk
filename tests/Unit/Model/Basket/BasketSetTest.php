<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Unit\Model\Basket;

use AboutYou\SDK\Model\Basket\BasketSet;
use AboutYou\SDK\Test\Unit\Model\AbstractModelTest;

class BasketSetTest extends AbstractModelTest
{
    public function testConstruct()
    {
        $basketSet  = new BasketSet('12', array('description' => 'blah', 'image_url'=> 'http://example.com/x.jpg'));
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Basket\\BasketSet', $basketSet);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructMissingImageUrlFailed()
    {
        $basketSet  = new BasketSet('12', array('description' => 'blah'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructMissingDescriptionFailed()
    {
        $basketSet  = new BasketSet('14', array('image_url'=> 'http://example.com/x.jpg'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructWithWrongID()
    {
        $basketSet = new BasketSet(12, array('description' => 'test', 'image_url' => 'http://img-url'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage image_url is required in additional data
     */
    public function testSetAdditionalDataFailed()
    {
        $basketSet = new BasketSet('12', array('description' => 'test', 'image_url' => 'http://img-url'));
        $basketSet->setAdditionData(array('description' => 'test'));
    }
}
 