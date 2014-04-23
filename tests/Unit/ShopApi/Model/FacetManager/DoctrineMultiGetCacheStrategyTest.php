<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Unit\Model\FacetManager;

use Collins\ShopApi\Model\Facet;
use Collins\ShopApi\Model\FacetManager\DoctrineMultiGetCacheStrategy;
use Collins\ShopApi\Test\Unit\Model\AbstractModelTest;

class DoctrineMultiGetCacheStrategyTest extends AbstractModelTest
{
    public function testFetch()
    {
        // $id, $name, $value, $groupId, $groupName
        $brand123 = new Facet(123, 'Brand 123', 'brand123', 0, 'brand');
        $brand456 = new Facet(456, 'Brand 456', 'brand465', 0, 'brand');
        $brand798 = new Facet(789, 'Brand 789', 'brand789', 0, 'brand');
        $size30   = new Facet(30, '30', '30', 1, 'size');
        $size40   = new Facet(40, '40', '40', 1, 'size');
        $cachedFacets = array(
            $brand123->getUniqueKey() => $brand123,
            $size30->getUniqueKey() => $size30
        );
        $fetchedFacets = array(
            $brand456->getUniqueKey() => $brand456,
            $brand798->getUniqueKey() => $brand798,
            $size40->getUniqueKey() => $size40
        );
        $expectedMultiGet = array(
            $brand123->getUniqueKey(),
            $brand456->getUniqueKey(),
            $brand798->getUniqueKey(),
            $size30->getUniqueKey(),
            $size40->getUniqueKey()
        );
        $expectedFetchIds = array(
            $brand456->getGroupId() => array($brand456->getId(), $brand798->getId()),
            $size40->getGroupId() => array($size40->getId())
        );

        $cacheMock = $this->getMockForAbstractClass('Doctrine\\Common\\Cache\\CacheMultiGet');
        $cacheMock->expects($this->atLeastOnce())
            ->method('fetchMulti')
            ->with($expectedMultiGet)
            ->will($this->returnValue($cachedFacets))
        ;
        $strategyMock = $this->getMockForAbstractClass('\\Collins\\ShopApi\\Model\\FacetManager\\FetchStrategyInterface');
        $strategyMock->expects($this->atLeastOnce())
            ->method('fetch')
            ->with($expectedFetchIds)
            ->will($this->returnValue($fetchedFacets))
        ;

        $facetIds = array(
            $brand123->getGroupId() => array($brand123->getId(), $brand456->getId(), $brand798->getId()),
            $size30->getGroupId()   => array($size30->getId(), $size40->getId())
        );
        $strategy = new DoctrineMultiGetCacheStrategy($cacheMock, $strategyMock);
        $facets = $strategy->fetch($facetIds);
        $this->assertCount(5, $facets);
    }
}
 