<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test;

class ShopSdkTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->resetAbstractModelShopApi('\\Collins\\ShopApi\\Model\\Autocomplete');
        $this->resetAbstractModelShopApi('\\Collins\\ShopApi\\Model\\BasketItem');
        $this->resetAbstractModelShopApi('\\Collins\\ShopApi\\Model\\Category');
        $this->resetAbstractModelShopApi('\\Collins\\ShopApi\\Model\\CategoriesResult');
        $this->resetAbstractModelShopApi('\\Collins\\ShopApi\\Model\\CategoryTree');
        $this->resetAbstractModelShopApi('\\Collins\\ShopApi\\Model\\FacetGroupSet');
        $this->resetAbstractModelShopApi('\\Collins\\ShopApi\\Model\\Image');
        $this->resetAbstractModelShopApi('\\Collins\\ShopApi\\Model\\Product');
        $this->resetAbstractModelShopApi('\\Collins\\ShopApi\\Model\\Variant');
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