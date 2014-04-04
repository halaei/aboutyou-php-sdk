<?php
namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class FacetTestAbstract extends AbstractShopApiTest
{
    /**
     *
     */
    public function testFacet()
    {
        $shopApi = $this->getShopApiWithResultFile('fetch-facet.json');

        $facets = $shopApi->fetchFacet(array(
            array("id" => 1234, "group_id" => 0 ),
            array("id" => 1234, "group_id" => 0 )
        ));
        $this->assertInternalType('array', $facets);

        foreach ($facets as $facet) {
            $this->assertInstanceOf('Collins\\ShopApi\\Model\\Facet', $facet);
            $this->assertInternalType('int', $facet->getId());
            $this->assertInternalType('string', $facet->getName());
            $this->assertInternalType('int', $facet->getGroupId());
            $this->assertInternalType('string', $facet->getGroupName());
            $this->assertEquals('brand', $facet->getGroupName());
            $this->assertEquals(0, $facet->getGroupId());
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFacetsEmptyParams()
    {
        $shopApi = $this->getShopApiWithResultFile('fetch-facet.json');

        $shopApi->fetchFacets(array());
    }
}
