<?php

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi\Criteria\ProductSearchCriteria;
use Collins\ShopApi\Model\Product;
use Collins\ShopApi\Model\ProductSearchResult;

/**
 * @group facet-manager
 */
class FacetManagerTest extends AbstractShopApiTest
{
    public function testProductSearch()
    {
        $shopApi = $this->getShopApiWithResultFile(
            'product_search-result.json',
            array('172', '206', '194', '2', '1', '0:4487')
        );

        $productSearchResult = $shopApi->fetchProductSearch($shopApi->getProductSearchCriteria('12345'));
        $products = $productSearchResult->getProducts();

        $brand = $products[0]->getBrand();
    }

    public function testProductByEans()
    {
        $shopApi = $this->getShopApiWithResultFile(
            'products_eans-result.json',
            array('172', '206', '194', '2', '1', '0:4487')
        );

        $productEansResult = $shopApi->fetchProductsByEans(array('dummy'));
        $products = $productEansResult->getProducts();

        $brand = $products[0]->getBrand();
    }

    public function testProductByIds()
    {
        $shopApi = $this->getShopApiWithResultFile(
            'products-result.json',
            array('172', '206', '194', '2', '1', '0:4487')
        );

        $productResult = $shopApi->fetchProductsByIds(array('dummy'));
        $products      = $productResult->getProducts();

        $brand = $products[301673]->getBrand();
    }

    public function testAutocomplete()
    {
        $shopApi = $this->getShopApiWithResultFile(
            'autocompletion-result.json',
            array('172', '206', '194', '2', '1', '0:4487')
        );

        $autocompletionResult = $shopApi->fetchAutocomplete('dummy');
        $products = $autocompletionResult->getProducts();

        $brand = $products[0]->getBrand();
    }

    public function testBasket()
    {
        $shopApi = $this->getShopApiWithResultFile(
            'basket-result.json',
            array('172', '206', '194', '2', '1', '0:4487')
        );

        $basket = $shopApi->fetchBasket('dummy');
        $products = $basket->getProducts();
        $product = reset($products);

        $brand = $product->getBrand();
    }


    protected function getShopApiWithResultFile($filename, $expectedMuilitGet)
    {
//        $shopApi = parent::getShopApiWithResultFiles(
//            $filename,
//            'facets-all.json'
//        );
        $shopApi = parent::getShopApiWithResultFile(
            $filename
        );
        $facetManager = $shopApi->getResultFactory()->getFacetManager();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\FacetManager', $facetManager);
        $cacheMock = $this->getMockForAbstractClass('Doctrine\\Common\\Cache\\CacheMultiGet');
        $cacheMock->expects($this->atLeastOnce())
            ->method('fetchMulti')
            ->with($expectedMuilitGet)
//            ->will($this->returnValue($this->getFacets()))
        ;
        $facetManager->setCache($cacheMock);

        return $shopApi;
    }

    protected function getJsonStringFromFile($filepath)
    {
        if (strpos($filepath, '/') !== 0) {
            $filepath = __DIR__.'/testData/' . $filepath;
        }
        $jsonString = file_get_contents($filepath);

        return $jsonString;
    }

    public function getFacets()
    {
        return array();
    }
}