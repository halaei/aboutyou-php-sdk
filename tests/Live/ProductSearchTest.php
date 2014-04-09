<?php

namespace Collins\ShopApi\Test\Live;

class ProductSearchTest extends \Collins\ShopApi\Test\Live\AbstractShopApiLiveTest
{
    
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
    }    
    
}

