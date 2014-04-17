<?php

namespace Collins\ShopApi\Test\Live;

use Collins\ShopApi\Model\FacetManager\DefaultFacetManager;
use Collins\ShopApi\Model\FacetManager\DoctrineMultiGetCacheStrategy;
use Doctrine\Common\Cache\ArrayCache;

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
        $facetManager = $shopApi->getFacetManager();
        /** @var DoctrineMultiGetCacheStrategy $doctrineMultiGetCacheStrategy */
        $doctrineMultiGetCacheStrategy = $facetManager->getFetchStratey();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\FacetManager\\DoctrineMultiGetCacheStrategy', $doctrineMultiGetCacheStrategy);

        $doctrineMultiGetCacheStrategy->cacheAllUsedFacets($shopApi);

        echo '<pre>', __LINE__, ') ', __METHOD__, ': <b>$cache</b>=', var_export($cache), '</pre>', PHP_EOL;

    }
}