<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Unit\Model;

use Collins\ShopApi\Model\Variant;
use Collins\ShopApi;

class VariantTest extends AbstractModelTest
{
    public function setup()
    {
        // setup DefaultModelFactory
        new ShopApi('app id', 'app token');
    }

    public function testFromJson()
    {
        $jsonObject = $this->getJsonObject('variant.json');

        $variant = new Variant($jsonObject);

        $this->assertEquals(5145543, $variant->getId());
        $this->assertEquals('ean1', $variant->getEan());
        $this->assertFalse($variant->isDefault());
        $this->assertEquals(3990, $variant->getPrice());
        $this->assertEquals(0, $variant->getOldPrice());
        $this->assertEquals(3995, $variant->getRetailPrice());
        $this->assertEquals(5, $variant->getQuantity());
        $this->assertEquals('2014-02-14 18:09:38', $variant->getFirstActiveDate());
        $this->assertEquals('2014-01-10 10:10:00', $variant->getFirstSaleDate());

        $images = $variant->getImages();
        $this->assertCount(2, $images);
        $this->assertInstanceOf('Collins\ShopApi\Model\Image', $images[0]);
        $this->assertInstanceOf('Collins\ShopApi\Model\Image', $images[1]);
        $this->assertArrayNotHasKey(2, $images);

        return $variant;
    }

    /**
     * @depends testFromJson
     */
    public function testVariantWithAttributs(Variant $variant)
    {
        $facetGroupSet = $variant->getFacetGroupSet();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\FacetGroupSet', $facetGroupSet);
        $this->assertCount(6, $facetGroupSet->getLazyGroups());

        $this->markTestIncomplete('This Test is not implemented yet');

//        $group = $variant->getFacetGroup(206);
//        $this->assertInstanceOf('Collins\\ShopApi\\Model\\FacetGroup', $group);
//        $this->assertNull($variant->getFacetGroup(1234));
    }

    public function testFromJsonAdditionalInfo()
    {
        $jsonObject = json_decode('{"additional_info":null}');
        $variant = new Variant($jsonObject);
        $this->assertEquals(null, $variant->getAdditionalInfo());

        $expected = json_decode('{"some":"data"}');
        $jsonObject = json_decode('{"additional_info":{"some":"data"}}');
        $variant = new Variant($jsonObject);
        $this->assertEquals($expected, $variant->getAdditionalInfo());
        $this->assertEquals("data", $variant->getAdditionalInfo()->some);

        return $variant;
    }
}