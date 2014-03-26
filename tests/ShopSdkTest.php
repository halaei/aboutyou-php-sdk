<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test;

/**
 * @backupStaticAttributes disabled
 */
class ShopSdkTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->resetAbstractModelShopApi('\\Collins\\ShopApi\\Model\\FacetGroupSet');
//        $this->resetAbstractModelShopApi('\\Collins\\ShopApi\\Model\\Image');
        $this->resetAbstractModelShopApi('\\Collins\\ShopApi\\Model\\Product');
//        $this->resetAbstractModelShopApi('\\Collins\\ShopApi\\Model\\Variant');
    }

    /**
     * Dummy method to avoid warning "no tests found"
     */
    public function testDummy()
    {
        $this->assertEquals(1,1);
    }

    protected function resetAbstractModelShopApi($className)
    {
        $class = new \ReflectionClass($className);
        $property = $class->getProperty('shopApi');
        $property->setAccessible(true);
        $property->setValue(null);
    }
}
