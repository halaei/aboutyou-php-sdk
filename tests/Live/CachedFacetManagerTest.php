<?php

namespace Collins\ShopApi\Test\Live;

use Collins\ShopApi\Factory\DefaultModelFactory;
use Collins\ShopApi\Model\Facet;
use Collins\ShopApi\Model\FacetManager\DefaultFacetManager;
use Collins\ShopApi\Model\FacetManager\AboutyouCacheStrategy;
use Aboutyou\Common\Cache\ArrayCache;
use Aboutyou\Common\Cache\CacheMultiGet;
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
        /** @var AboutyouCacheStrategy $cacheStrategy */
        $cacheStrategy = $facetManager->getFetchStrategy();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\FacetManager\\AboutyouCacheStrategy', $cacheStrategy);

        $cacheStrategy->cacheAllFacets($shopApi);

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

        $strategy = new AboutyouCacheStrategy($cache, $strategyMock);
        /** @var DefaultModelFactory $factory */
        $facetManager    = new DefaultFacetManager($strategy);
        $this->eventDispatcher = new EventDispatcher();
        $factory = new DefaultModelFactory($shopApi, $facetManager, new EventDispatcher());
        $shopApi->setResultFactory($factory);

        $criteria = $shopApi->getProductSearchCriteria('AboutyouCacheStrategy')
            ->setLimit(0)
//            ->selectFacetsByGroupId(172, 3)
            ->selectAllFacets(\Collins\ShopApi\Criteria\ProductSearchCriteria::FACETS_UNLIMITED)
        ;

        $productSearchResult = $shopApi->fetchProductSearch($criteria);
        $facetCounts = $productSearchResult->getFacets();
    }
}
