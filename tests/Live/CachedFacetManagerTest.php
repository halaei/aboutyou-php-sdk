<?php

namespace AboutYou\SDK\Test\Live;

use AboutYou\SDK\Factory\DefaultModelFactory;
use AboutYou\SDK\Model\Category;
use AboutYou\SDK\Model\CategoryManager\DefaultCategoryManager;
use AboutYou\SDK\Model\Facet;
use AboutYou\SDK\Model\FacetManager\DefaultFacetManager;
use AboutYou\SDK\Model\FacetManager\AboutyouCacheStrategy;
use Aboutyou\Common\Cache\ArrayCache;
use Aboutyou\Common\Cache\CacheMultiGet;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @group live
 */
class CachedFacetManagerTest extends \AboutYou\SDK\Test\Live\AbstractShopApiLiveTest
{
    public function testPreCacheFacets()
    {
        $cache   = new ArrayCache();
        $shopApi = $this->getShopApi(null, null, $cache);
        $facetManager = $shopApi->getResultFactory()->getFacetManager();
        $this->assertTrue($facetManager->isEmpty());
        $shopApi->preCache();
        $this->assertFalse($facetManager->isEmpty());

        /** @var DefaultFacetManager $facetManager */
        $facetManager = new DefaultFacetManager($cache, $shopApi->getAppId());
        $this->assertFalse($facetManager->isEmpty());

        return $cache;
    }
}
