<?php

namespace Collins\ShopApi\Test\Live;

class ProductSearchTest extends \Collins\ShopApi\Test\Live\AbstractShopApiLiveTest
{
    /**
     * @group live
     */
    public function testProductSearchWithLimit()
    {
        $api = $this->getShopApi();           
        $criteria = $this->getSearchCriteria();
        $criteria->setLimit(5);
        
        $result = $api->fetchProductSearch($criteria);
        
        $this->assertInstanceOf('\Collins\ShopApi\Model\ProductSearchResult', $result);         
        $this->assertCount(5, $result->getProducts()); 
        
        foreach($result->getProducts() as $product) {
            $this->assertInstanceOf('\Collins\ShopApi\Model\Product', $product);
        }
    }
    
    /**
     * @group live
     */
    public function testProductSearchWithId()
    {
        $api = $this->getShopApi();        
        $criteria = $this->getSearchCriteria();
        $criteria->setLimit(5);
        
        $result = $api->fetchProductSearch($criteria);
        
        $productsArray = $result->getProducts();
        $product = $productsArray[0];
        
        $this->assertInstanceOf('\Collins\ShopApi\Model\Product', $product);         
        
        $resultProduct = $api->fetchProductsByIds(array($product->getId()));
        
        $this->assertInstanceOf('\Collins\ShopApi\Model\ProductsResult', $resultProduct); 
    }
    
    /**
     * @group live
     */
    public function testProductSearchCategoryTree()
    {
        $api = $this->getShopApi();        
        $criteria = $this->getSearchCriteria();
        $criteria->setLimit(5);
        
        $result = $api->fetchProductSearch($criteria);

        $this->assertInternalType('array', $result->getCategoryTree());
    }
    
    /**
     * @group live
     */
    public function testProductSearchCategory()
    {
        $api = $this->getShopApi();        
        $criteria = $this->getSearchCriteria();
        $criteria->setLimit(5);
        
        $result = $api->fetchProductSearch($criteria);

        $this->assertInternalType('array', $result->getCategories());
    }    
    
    /**
     * @group live
     */
    public function testProductSearchProductCount()
    {
        $api = $this->getShopApi();        
        $criteria = $this->getSearchCriteria();
        $criteria->setLimit(5);
        
        $result = $api->fetchProductSearch($criteria);

        $this->assertInternalType('int', $result->getProductCount());
        $this->assertCount(5, $result->getProducts());
    } 
    
    /**
     * @group live
     */
    public function ProductSearchSort()
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

        $rawFacets = $productSearchResult->getRawFacets();
        $this->assertInstanceOf('\stdClass', $rawFacets);
        $this->assertObjectHasAttribute("0", $rawFacets);
        $brandFacets = $rawFacets->{"0"};
        $this->assertInstanceOf('\stdClass', $brandFacets);
        $this->assertObjectHasAttribute('_type', $brandFacets);
        $this->assertObjectHasAttribute('total', $brandFacets);
        $this->assertObjectHasAttribute('terms', $brandFacets);
        $this->assertObjectHasAttribute('other', $brandFacets);
        $this->assertObjectHasAttribute('missing', $brandFacets);
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

