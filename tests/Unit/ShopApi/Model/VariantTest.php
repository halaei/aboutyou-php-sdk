<?php
/**
 * @auther nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Unit\Model;

use Collins\ShopApi\Model\Variant;
use Collins\ShopApi;

class VariantTest extends AbstractModelTest
{
    public function testFromJson()
    {
        $jsonObject = $this->getJsonObject('variant.json');

        $variant = Variant::createFromJson($jsonObject, $this->getModelFactory(), $this->getProduct());

        $this->assertEquals(5145543, $variant->getId());
        $this->assertEquals('ean1', $variant->getEan());
        $this->assertFalse($variant->isDefault());
        $this->assertEquals(3990, $variant->getPrice());
        $this->assertEquals(0, $variant->getOldPrice());
        $this->assertEquals(3995, $variant->getRetailPrice());
        $this->assertEquals(5, $variant->getQuantity());
        $this->assertEquals(new \DateTime('2014-02-14 18:09:38'), $variant->getFirstActiveDate());
        $this->assertEquals(new \DateTime('2014-01-10 10:10:00'), $variant->getFirstSaleDate());
        $this->assertEquals(new \DateTime('2014-01-11 10:11:00'), $variant->getCreatedDate());
        $this->assertEquals(new \DateTime('2014-01-12 10:12:00'), $variant->getUpdatedDate());

        $images = $variant->getImages();
        $this->assertCount(2, $images);
        $this->assertInstanceOf('Collins\ShopApi\Model\Image', $images[0]);
        $this->assertInstanceOf('Collins\ShopApi\Model\Image', $images[1]);
        $this->assertArrayNotHasKey(2, $images);

        return $variant;
    }

    public function testFromJson2()
    {
        $jsonObject = $this->getJsonObject('variant2.json');

        $variant = Variant::createFromJson($jsonObject, $this->getModelFactory(), $this->getProduct());
        $this->assertNull($variant->getFirstActiveDate());
        $this->assertNull($variant->getFirstSaleDate());
        $this->assertNull($variant->getCreatedDate());
        $this->assertNull($variant->getUpdatedDate());
    }

    /**
     * @depends testFromJson
     */
    public function testVariantWithAttributes(Variant $variant)
    {
        $facetGroupSet = $variant->getFacetGroupSet();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\FacetGroupSet', $facetGroupSet);
        $this->assertCount(7, $facetGroupSet->getLazyGroups());

        $this->markTestIncomplete('This Test is not implemented yet');

//        $group = $variant->getFacetGroup(206);
//        $this->assertInstanceOf('Collins\\ShopApi\\Model\\FacetGroup', $group);
//        $this->assertNull($variant->getFacetGroup(1234));
    }

    public function testFromJsonAdditionalInfo()
    {
        $jsonObject = json_decode('{"additional_info":null}');
        $variant = Variant::createFromJson($jsonObject, $this->getModelFactory(), $this->getProduct());
        $this->assertEquals(null, $variant->getAdditionalInfo());

        $expected = json_decode('{"some":"data"}');
        $jsonObject = json_decode('{"additional_info":{"some":"data"}}');
        $variant = Variant::createFromJson($jsonObject, $this->getModelFactory(), $this->getProduct());
        $this->assertEquals($expected, $variant->getAdditionalInfo());
        $this->assertEquals("data", $variant->getAdditionalInfo()->some);

        return $variant;
    }

    /**
     * @depends testFromJson
     */
    public function testGetImageByHash(Variant $variant)
    {
        $images = $variant->getImages();
        $this->assertEquals($images[0], $variant->getImageByHash($images[0]->getHash()));
        $this->assertEquals($images[1], $variant->getImageByHash($images[1]->getHash()));
        $this->assertNotEquals($images[0], $variant->getImageByHash($images[1]->getHash()));
        $this->assertNull($variant->getImageByHash('unknown'));
    }

    /**
     * @depends testFromJson
     */
    public function testGetSize(Variant $variant)
    {
        $facetManager = $this->getFacetManager('facets-all.json');
        ShopApi\Model\FacetGroupSet::setFacetManager($facetManager);

        $size = $variant->getSize();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\FacetGroup', $size);
        $this->assertEquals('XS', $size->getFacetNames());
    }

    /**
     * @depends testFromJson
     */
    public function testGetSeasonCode(Variant $variant)
    {
        $facetManager = $this->getFacetManager('facets-all.json');
        ShopApi\Model\FacetGroupSet::setFacetManager($facetManager);

        $facetGroup = $variant->getSeasonCode();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\FacetGroup', $facetGroup);
        $this->assertEquals('season', $facetGroup->getName());
        $this->assertEquals('HW 14', $facetGroup->getFacetNames());

        $facet = $facetManager->getFacet(289, 4084);
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Facet', $facet);
        $this->assertEquals('hw14', $facet->getValue());
        $this->assertEquals('HW 14', $facet->getName());
        $this->assertEquals('season', $facet->getGroupName());
    }

    protected function getFacetManager($filename)
    {
        $jsonObject = $this->getJsonObject($filename);
        if (isset($jsonObject[0]->facets->facet)) {
            $jsonFacets = $jsonObject[0]->facets->facet;
        } else {
            $jsonFacets = $jsonObject[0]->facet;
        }
        $facets = array();
        foreach ($jsonFacets as $jsonFacet) {
            $facet = ShopApi\Model\Facet::createFromJson($jsonFacet);
            $facets[] = $facet;
        }

        $facetManager = new ShopApi\Model\FacetManager\StaticFacetManager($facets);

        return $facetManager;
    }
    
    private function getProduct() 
    {
        $json = json_decode('{"id":1,"name":"Product"}');
        return ShopApi\Model\Product::createFromJson($json, $this->getModelFactory(), 1);        
    }    


//    2014-04-21 nils.droege: not finish yet
//
//    protected function getFacetManagerMock($facetsData)
//    {
//        $facetsMap = array();
//        foreach ($facetsData as $facetData) {
//            $facet = new ShopApi\Model\Facet($facetData[0], $facetData[1], '', $facetData[2], $facetData[3]);
//            $facetsMap[$facet->getUniqueKey()] = $facet;
//        }
//        $facetManager = $this->getMockForAbstractClass('\\Collins\\ShopApi\\Model\\FacetManager\\FacetManagerInterface');
//        $facetManager->expects($this->any())
//            ->method('getFacet')
//            ->with($this->returnValueMap($facetsMap))
//        ;
//
//        return $facetManager;
//    }
}
