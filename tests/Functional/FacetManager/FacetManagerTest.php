<?php

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;
use Collins\ShopApi\Model\Basket;
use Collins\ShopApi\Model\ProductSearchResult;
use Collins\ShopApi\Model\FacetManager;
use Doctrine\Common\Cache\ArrayCache;

/**
 * @group facet-manager
 */
class FacetManagerTest extends AbstractShopApiTest
{
    protected function getShopApiWithGroupFacetStrategy($filename)
    {
        $shopApi = $this->getShopApiWithResultFiles(array(
            $filename,
            'facets-for-product-variant-facets.json',
        ));

        return $shopApi;
    }

    protected function getShopApiWithSingleFacetStrategy($filename)
    {
        $shopApi = $this->getShopApiWithResultFiles(array(
            $filename,
            'facet-for-product-variant-facets.json',
        ));
        $facetManager =  new FacetManager\DefaultFacetManager(new FacetManager\FetchSingleFacetStrategy($shopApi));
        $shopApi->getResultFactory()->setFacetManager($facetManager);

        return $shopApi;
    }

    public function testProductSearch()
    {
        $shopApi = $this->getShopApiWithSingleFacetStrategy(
            'product_search-result.json'
        );

        $productSearchResult = $shopApi->fetchProductSearch($shopApi->getProductSearchCriteria('12345'));
        $products = $productSearchResult->getProducts();

        $brand = $products[0]->getBrand();
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Facet', $brand);

        $shopApi = $this->getShopApiWithGroupFacetStrategy(
            'product_search-result.json'
        );

        $productSearchResult = $shopApi->fetchProductSearch($shopApi->getProductSearchCriteria('12345'));
        $products = $productSearchResult->getProducts();

        $brand = $products[0]->getBrand();
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Facet', $brand);
    }

    public function testProductByEans()
    {
        $shopApi = $this->getShopApiWithSingleFacetStrategy(
            'products_eans-result.json'
        );

        $productEansResult = $shopApi->fetchProductsByEans(array('dummy'));
        $products = $productEansResult->getProducts();

        $brand = $products[0]->getBrand();
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Facet', $brand);

        $shopApi = $this->getShopApiWithGroupFacetStrategy(
            'products_eans-result.json'
        );

        $productEansResult = $shopApi->fetchProductsByEans(array('dummy'));
        $products = $productEansResult->getProducts();

        $brand = $products[0]->getBrand();
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Facet', $brand);
    }

    public function testProductByIds()
    {
        $shopApi = $this->getShopApiWithSingleFacetStrategy(
            'products-result.json'
        );

        $productResult = $shopApi->fetchProductsByIds(array('dummy'));
        $products      = $productResult->getProducts();

        $brand = $products[301673]->getBrand();
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Facet', $brand);

        $shopApi = $this->getShopApiWithGroupFacetStrategy(
            'products-result.json'
        );

        $productResult = $shopApi->fetchProductsByIds(array('dummy'));
        $products      = $productResult->getProducts();

        $brand = $products[301673]->getBrand();
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Facet', $brand);
    }

    public function testAutocomplete()
    {
        $shopApi = $this->getShopApiWithSingleFacetStrategy(
            'autocompletion-result.json'
        );

        $autocompletionResult = $shopApi->fetchAutocomplete('dummy');
        $products = $autocompletionResult->getProducts();

        $brand = $products[0]->getBrand();
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Facet', $brand);

        $shopApi = $this->getShopApiWithGroupFacetStrategy(
            'autocompletion-result.json'
        );

        $autocompletionResult = $shopApi->fetchAutocomplete('dummy');
        $products = $autocompletionResult->getProducts();

        $brand = $products[0]->getBrand();
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Facet', $brand);
    }

    public function testBasket()
    {
        $shopApi = $this->getShopApiWithSingleFacetStrategy(
            'basket-result.json'
        );

        $basket = $shopApi->fetchBasket('dummy');
        $products = $basket->getProducts();
        $product = reset($products);

        $brand = $product->getBrand();
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Facet', $brand);

        $shopApi = $this->getShopApiWithGroupFacetStrategy(
            'basket-result.json'
        );

        $basket = $shopApi->fetchBasket('dummy');
        $products = $basket->getProducts();
        $product = reset($products);

        $brand = $product->getBrand();
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Facet', $brand);
    }

    public function testGetOrder()
    {
        $shopApi = $this->getShopApiWithSingleFacetStrategy(
            'get_order-result.json'
        );

        $order = $shopApi->fetchOrder('dummy');
        $products = $order->getBasket()->getProducts();
        $product = reset($products);

        $brand = $product->getBrand();
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Facet', $brand);

        $shopApi = $this->getShopApiWithGroupFacetStrategy(
            'get_order-result.json'
        );

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
        $shopApi = $this->getShopApiWithSingleFacetStrategy(
            'get_order-failed.json'
        );

        $order = $shopApi->fetchOrder('dummy');
    }

    public function testCacheStrategy()
    {
        $cache   = new ArrayCache();
        $shopApi = new ShopApi('id', 'pw', ShopApi\Constants::API_ENVIRONMENT_STAGE, null, null, $cache);
        /** @var ShopApi\Model\FacetManager\DefaultFacetManager $facetManager */
        $facetManager = $shopApi->getResultFactory()->getFacetManager();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\FacetManager\\DoctrineMultiGetCacheStrategy', $facetManager->getFetchStrategy());
    }

    protected function getJsonStringFromFile($filepath)
    {
        return parent::getJsonStringFromFile($filepath, __DIR__);
    }
}