<?php

namespace AboutYou\SDK\Test\Live;

use AboutYou\SDK\Model\ProductSearchResult;

/**
 * @group live
 */
class ProductSearchTest extends \AboutYou\SDK\Test\Live\AbstractShopApiLiveTest
{
    public function testProductSearchWithLimit()
    {
        $api = $this->getShopApi();
        $criteria = $this->getSearchCriteria();
        $criteria->setLimit(5);

        $result = $api->fetchProductSearch($criteria);

        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\ProductSearchResult', $result);
        $this->assertCount(5, $result->getProducts());

        foreach($result->getProducts() as $product) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Product', $product);
        }
    }

    public function testProductSearchWithId()
    {
        $api = $this->getShopApi();
        $criteria = $this->getSearchCriteria();
        $criteria->setLimit(5);

        $result = $api->fetchProductSearch($criteria);

        $productsArray = $result->getProducts();
        $product = $productsArray[0];

        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Product', $product);

        $resultProduct = $api->fetchProductsByIds(array($product->getId()));

        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\ProductsResult', $resultProduct);
    }

    public function testProductSearchWithEANS()
    {
        $api = $this->getShopApi();

        $result = $api->fetchProductsByEans(['NO_VALID_EAN']);
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\ProductsEansResult', $result);

        $this->assertCount(1, $result->getErrors());
    }

    public function testProductSearchCategoryTree()
    {
        $api = $this->getShopApi();
        $criteria = $this->getSearchCriteria();
        $criteria->setLimit(5);

        $result = $api->fetchProductSearch($criteria);

        $this->assertInternalType('array', $result->getCategoryTree());
    }

    public function testProductSearchCategory()
    {
        $api = $this->getShopApi();
        $criteria = $this->getSearchCriteria();
        $criteria->setLimit(5);

        $result = $api->fetchProductSearch($criteria);

        $this->assertInternalType('array', $result->getCategories());
    }

    public function testProductSearchProductCount()
    {
        $api = $this->getShopApi();
        $criteria = $this->getSearchCriteria();
        $criteria->setLimit(5);

        $result = $api->fetchProductSearch($criteria);

        $this->assertInternalType('int', $result->getProductCount());
        $this->assertCount(5, $result->getProducts());
    }

    public function testProductSearchProductBoosts()
    {
        $api = $this->getShopApi();
        $criteria = $this->getSearchCriteria()
            ->boostProducts(array(1))
            ->setLimit(2)
        ;

        $result = $api->fetchProductSearch($criteria);

        $this->assertInternalType('int', $result->getProductCount());
    }

    public function testProductSearchSort()
    {
        $shopApi = $this->getShopApi();

        // search products and sort
        $criteria = $shopApi->getProductSearchCriteria($this->getSessionId())
            ->sortBy(
                \AboutYou\SDK\Criteria\ProductSearchCriteria::SORT_TYPE_MOST_VIEWED
            )
        ;
        $productSearchResult = $shopApi->fetchProductSearch($criteria);
        $this->checkProductSearchResult($productSearchResult);
    }

    public function testProductSearchWithFacets()
    {
        $shopApi = $this->getShopApi();

        // search products and sort
        $criteria = $shopApi->getProductSearchCriteria($this->getSessionId())
            ->selectSales()
            ->selectPriceRanges()
            ->selectFacetsByGroupId(0, 10)
        ;
        $productSearchResult = $shopApi->fetchProductSearch($criteria);
        $this->checkProductSearchResult($productSearchResult);

        $saleFacets = $productSearchResult->getSaleCounts();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\ProductSearchResult\\SaleCounts', $saleFacets);
        $this->assertInternalType('integer', $saleFacets->getProductCountTotal());
        $this->assertInternalType('integer', $saleFacets->getProductCountInSale());
        $this->assertInternalType('integer', $saleFacets->getProductCountNotInSale());

        $priceRanges = $productSearchResult->getPriceRanges();
        $this->assertInternalType('array', $priceRanges);

        $facets =  $productSearchResult->getFacets();
        $this->assertInternalType('array', $facets);
        $brandFacets = $facets[0];
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\ProductSearchResult\\FacetCounts', $brandFacets);
        $this->assertInternalType('integer', $brandFacets->getProductCountTotal());
        $this->assertEquals(0, $brandFacets->getGroupId());
        $facetCounts = $brandFacets->getFacetCounts();
        $this->assertInternalType('array', $facetCounts);
        $this->assertCount(10, $facetCounts);
        foreach ($facetCounts as $facetCount) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\ProductSearchResult\\FacetCount', $facetCount);
            $this->assertInternalType('integer', $facetCount->getProductCount());
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Facet', $facetCount->getFacet());
        }
    }

    private function checkProduct(Product $product)
    {
        $this->assertObjectHasAttribute('id', $product);
        $this->assertObjectHasAttribute('name', $product);
    }

    private function checkProductSearchResult(ProductSearchResult $products)
    {
        foreach ($products as $product) {
            $this->checkProduct($product);
        }
    }
}

