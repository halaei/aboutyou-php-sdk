<?php

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi\Criteria\ProductSearchCriteria;
use Collins\ShopApi\Model\Basket;
use Collins\ShopApi\Model\Product;
use Collins\ShopApi\Model\ProductSearchResult;
use Collins\ShopApi;
use Doctrine\Common\Cache\ArrayCache;

/**
 * @group facet-manager
 */
class FacetManagerTest extends AbstractShopApiTest
{
    public function testProductSearch()
    {
        $shopApi = $this->getShopApiWithResultFiles(array(
            'product_search-result.json',
            'facets-for-product-variant-facets.json',
        ));

        $k = $shopApi->getResultFactory()->getFacetManager();

        $productSearchResult = $shopApi->fetchProductSearch($shopApi->getProductSearchCriteria('12345'));
        $products = $productSearchResult->getProducts();

        $brand = $products[0]->getBrand();
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Facet', $brand);
    }

    public function testProductByEans()
    {
        $shopApi = $this->getShopApiWithResultFiles(array(
            'products_eans-result.json',
            'facets-for-product-variant-facets.json',
        ));

        $productEansResult = $shopApi->fetchProductsByEans(array('dummy'));
        $products = $productEansResult->getProducts();

        $brand = $products[0]->getBrand();
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Facet', $brand);
    }

    public function testProductByIds()
    {
        $shopApi = $this->getShopApiWithResultFiles(array(
            'products-result.json',
            'facets-for-product-variant-facets.json',
        ));

        $productResult = $shopApi->fetchProductsByIds(array('dummy'));
        $products      = $productResult->getProducts();

        $brand = $products[301673]->getBrand();
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Facet', $brand);
    }

    public function testAutocomplete()
    {
        $shopApi = $this->getShopApiWithResultFiles(array(
            'autocompletion-result.json',
            'facets-for-product-variant-facets.json'
        ));

        $autocompletionResult = $shopApi->fetchAutocomplete('dummy');
        $products = $autocompletionResult->getProducts();

        $brand = $products[0]->getBrand();
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Facet', $brand);
    }

    public function testBasket()
    {
        $shopApi = $this->getShopApiWithResultFiles(array(
            'basket-result.json',
            'facets-for-product-variant-facets.json',
        ));

        $basket = $shopApi->fetchBasket('dummy');
        $products = $basket->getProducts();
        $product = reset($products);

        $brand = $product->getBrand();
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Facet', $brand);
    }

    public function testGetOrder()
    {
        $shopApi = $this->getShopApiWithResultFiles(array(
            'get_order-result.json',
            'facets-for-product-variant-facets.json',
        ));

        $order = $shopApi->fetchOrder('dummy');
        $products = $order->getBasket()->getProducts();
        $product = reset($products);

        $brand = $product->getBrand();
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Facet', $brand);
    }

    /**
     * ensure, that the FacetManager isn't called or does not throw a different error
     * @expectedException \Collins\ShopApi\Exception\ResultErrorException
     */
    public function testGetOrderFailed()
    {
        $shopApi = $this->getShopApiWithResultFiles(array(
            'get_order-failed.json',
            'facets-for-product-variant-facets.json',
        ));

        $order = $shopApi->fetchOrder('dummy');
    }

    public function testCacheStrategy()
    {
        $cache   = new ArrayCache();
        $shopApi = new ShopApi('id', 'pw', ShopApi\Constants::API_ENVIRONMENT_STAGE, null, null, $cache);
        /** @var ShopApi\Model\FacetManager\DefaultFacetManager $facetManager */
        $facetManager = $shopApi->getResultFactory()->getFacetManager();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\FacetManager\\DoctrineMultiGetCacheStrategy', $facetManager->getFetchStratey());
    }

    protected function getJsonStringFromFile($filepath)
    {
        return parent::getJsonStringFromFile($filepath, __DIR__);
    }

//    protected function getShopApiWithResultFile($filename, $expectedMultiGet)
//    {
////        $shopApi = parent::getShopApiWithResultFiles(array(
////            $filename,
////            'facets-all.json'
////        ));
//        $shopApi = parent::getShopApiWithResultFile(
//            $filename
//        );
//        $facetManager = $shopApi->getResultFactory()->getFacetManager();
//        $this->assertInstanceOf('Collins\\ShopApi\\Model\\FacetManager\\AbstractFacetManager', $facetManager);
//        $cacheMock = $this->getMockForAbstractClass('Doctrine\\Common\\Cache\\CacheMultiGet');
//        $cacheMock->expects($this->atLeastOnce())
//            ->method('fetchMulti')
//            ->with($expectedMultiGet)
////            ->will($this->returnValue($this->getFacets()))
//        ;
//        $facetManager->setCache($cacheMock);
//
//        return $shopApi;
//    }
//
//    public function getFacets()
//    {
//        return array();
//    }
}