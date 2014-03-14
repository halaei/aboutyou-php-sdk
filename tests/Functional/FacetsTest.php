<?php
namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class FacetsTestAbstract extends AbstractShopApiTest
{
    /**
     *
     */
    public function testFacets()
    {
        $shopApi = $this->getShopApiWithResultFile('facets-206.json');

        $facets = $shopApi->fetchFacets(array(206));
        $this->assertInternalType('array', $facets);

        $count = 0;
        foreach ($facets as $facet) {
            $this->assertInstanceOf('Collins\\ShopApi\\Model\\Facet', $facet);
            $this->assertInternalType('int', $facet->getId());
            $this->assertInternalType('string', $facet->getName());
            $this->assertInternalType('string', $facet->getValue());
            $this->assertInternalType('int', $facet->getGroupId());
            $this->assertInternalType('string', $facet->getGroupName());
            $this->assertEquals('size_code', $facet->getGroupName());
            $this->assertEquals(206, $facet->getGroupId());
            if ($count++ > 2) break; // tree is enough
        }

        $facet = $facets[ShopApi\Model\Facet::uniqueKey(206, 2353)];
        $this->assertEquals($facet, reset($facets));
        $this->assertEquals(2353, $facet->getId());
        $this->assertEquals('01', $facet->getName());
    }

    /**
     * @expectedException \Collins\ShopApi\Exception\InvalidParameterException
     */
    public function testNotAllowFetchAllFacets()
    {
        $shopApi = $this->getShopApiWithResultFile('facets-206.json');

        $shopApi->fetchFacets(array());
    }
}
