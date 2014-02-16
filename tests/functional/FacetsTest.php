<?php
namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class FacetsTest extends ShopApiTest
{
    /**
     *
     */
    public function testFacets()
    {
        $shopApi = $this->getShopApiWithResultFile('facets-206.json');

        $facets = $shopApi->fetchAttributes([206]);
        $this->assertInternalType('array', $facets);

        foreach ($facets as $facet) {
            $this->assertInstanceOf('Collins\\ShopApi\\Model\\Attribute', $facet);
            $this->assertInternalType('int', $facet->getId());
            $this->assertInternalType('string', $facet->getName());
            $this->assertInternalType('string', $facet->getValue());
            $this->assertInternalType('int', $facet->getGroupId());
            $this->assertInternalType('string', $facet->getGroupName());
        }
    }

    /**
     * @expectedException \Collins\ShopApi\Exception\InvalidParameterException
     */
    public function testNotAllowFetchAllFacets()
    {
        $shopApi = $this->getShopApiWithResultFile('facets-206.json');

        $shopApi->fetchAttributes([]);
    }
}
