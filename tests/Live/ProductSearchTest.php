<?php

namespace Collins\ShopApi\Test\Live;

use Collins\ShopApi\Model\ProductSearchResult;

/**
 * @group live
 */
class ProductSearchTest extends \Collins\ShopApi\Test\Live\AbstractShopApiLiveTest
{
    public function testProductSearchWithLimit()
    {
        $api = $this->getShopApi();
        $criteria = $this->getSearchCriteria();
        $criteria->setLimit(5);
        
        $result = $api->fetchProductSearch($criteria);
        
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\ProductSearchResult', $result);
        $this->assertCount(5, $result->getProducts()); 
        
        foreach($result->getProducts() as $product) {
            $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Product', $product);
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
        
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Product', $product);
        
        $resultProduct = $api->fetchProductsByIds(array($product->getId()));
        
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\ProductsResult', $resultProduct);
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
                \Collins\ShopApi\Criteria\ProductSearchCriteria::SORT_TYPE_MOST_VIEWED
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
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\ProductSearchResult\\SaleCounts', $saleFacets);
        $this->assertInternalType('integer', $saleFacets->getProductCountTotal());
        $this->assertInternalType('integer', $saleFacets->getProductCountInSale());
        $this->assertInternalType('integer', $saleFacets->getProductCountNotInSale());

        $priceRanges = $productSearchResult->getPriceRanges();
        $this->assertInternalType('array', $priceRanges);

        $facets =  $productSearchResult->getFacets();
        $this->assertInternalType('array', $facets);
        $brandFacets = $facets[0];
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\ProductSearchResult\\FacetCounts', $brandFacets);
        $this->assertInternalType('integer', $brandFacets->getProductCountTotal());
        $this->assertEquals(0, $brandFacets->getGroupId());
        $facetCounts = $brandFacets->getFacetCounts();
        $this->assertInternalType('array', $facetCounts);
        $this->assertCount(10, $facetCounts);
        foreach ($facetCounts as $facetCount) {
            $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\ProductSearchResult\\FacetCount', $facetCount);
            $this->assertInternalType('integer', $facetCount->getProductCount());
            $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Facet', $facetCount->getFacet());
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

