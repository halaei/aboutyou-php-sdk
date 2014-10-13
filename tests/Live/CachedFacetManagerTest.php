<?php

namespace Collins\ShopApi\Test\Live;

use Collins\ShopApi\Factory\DefaultModelFactory;
use Collins\ShopApi\Model\Category;
use Collins\ShopApi\Model\CategoryManager\DefaultCategoryManager;
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
    public function testPreCacheFacets()
    {
        $cache   = new ArrayCache();
        $shopApi = $this->getShopApi(null, null, $cache);
        $facetManager = $shopApi->getResultFactory()->getFacetManager();
        $this->assertTrue($facetManager->isEmpty());
        $shopApi->preCacheFacets();
        $this->assertFalse($facetManager->isEmpty());

        /** @var DefaultFacetManager $facetManager */
        $facetManager = new DefaultFacetManager($cache, $shopApi->getAppId());
        $this->assertFalse($facetManager->isEmpty());

        return $cache;
    }
}
