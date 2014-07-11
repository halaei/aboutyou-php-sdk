<?php

namespace Collins\ShopApi\Test\Live;

use Collins\ShopApi\Factory\DefaultModelFactory;
use Collins\ShopApi\Model\Facet;
use Collins\ShopApi\Model\FacetManager\DefaultFacetManager;
use Collins\ShopApi\Model\FacetManager\DoctrineMultiGetCacheStrategy;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheMultiGet;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @group live
 */
class CachedFacetManagerTest extends \Collins\ShopApi\Test\Live\AbstractShopApiLiveTest
{
    public function testCacheAllUsedFacets()
    {
        $cache   = new ArrayCache();
        $shopApi = $this->getShopApi(null, null, $cache);
        /** @var DefaultFacetManager $facetManager */
        $facetManager = $shopApi->getResultFactory()->getFacetManager();
        /** @var DoctrineMultiGetCacheStrategy $doctrineMultiGetCacheStrategy */
        $doctrineMultiGetCacheStrategy = $facetManager->getFetchStrategy();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\FacetManager\\DoctrineMultiGetCacheStrategy', $doctrineMultiGetCacheStrategy);

        $doctrineMultiGetCacheStrategy->cacheAllFacets($shopApi);

        return $cache;
    }

    /**
     * @depends testCacheAllUsedFacets
     *
     * @param CacheMultiGet $cache
     */
    public function testThatFactesAreCached(CacheMultiGet $cache)
    {
        $shopApi = $this->getShopApi(null, null, $cache);

        $dummyFacet  = new Facet(30, '30', '30', 1, 'size');
        $fetchedFacets[$dummyFacet->getUniqueKey()] = $dummyFacet;

        $strategyMock = $this->getMockForAbstractClass('\\Collins\\ShopApi\\Model\\FacetManager\\FetchStrategyInterface');
        $strategyMock->expects($this->never())
            ->method('fetch')
            ->will($this->returnValue($fetchedFacets))
        ;

        $strategy = new DoctrineMultiGetCacheStrategy($cache, $strategyMock);
        /** @var DefaultModelFactory $factory */
        $facetManager    = new DefaultFacetManager($strategy);
        $this->eventDispatcher = new EventDispatcher();
        $factory = new DefaultModelFactory($shopApi, $facetManager, new EventDispatcher());
        $shopApi->setResultFactory($factory);

        $criteria = $shopApi->getProductSearchCriteria('DoctrineMultiGetCacheStrategy')
            ->setLimit(0)
//            ->selectFacetsByGroupId(172, 3)
            ->selectAllFacets(\Collins\ShopApi\Criteria\ProductSearchCriteria::FACETS_UNLIMITED)
        ;

        $productSearchResult = $shopApi->fetchProductSearch($criteria);
        $facetCounts = $productSearchResult->getFacets();
    }
}
