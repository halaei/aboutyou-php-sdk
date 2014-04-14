<?php

namespace Collins\ShopApi\Test\Live;

use Collins\ShopApi;

class CategoryTest extends ShopApi\Test\Live\AbstractShopApiLiveTest
{

    /**
     * @expectedException \InvalidArgumentException
     * @group live
     */
    public function testFetchCategoriesByIdWithStrings()
    {
        $api = $this->getShopApi();
        $api->fetchCategoriesByIds(';!');
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @group live
     */    
    public function testFetchCategoryTreeWithDepth()
    {
        $api = $this->getShopApi();
        $tree = $api->fetchCategoryTree(1000);
    }
    
    /**
     * @expectedException Collins\ShopApi\Exception\ResultErrorException
     * @group live
     */    
    public function testFetchCategoryTreeWithTrueDepth()
    {
        $api = $this->getShopApi();
        $tree = $api->fetchCategoryTree(true);
    }    

    /**
     * @expectedException Collins\ShopApi\Exception\ResultErrorException
     * @group live
     */    
    public function testFetchCategoryTreeWithFalseDepth()
    {
        $api = $this->getShopApi();
        $tree = $api->fetchCategoryTree(false);
    }    
    
    /**
     * @group live
     */
    public function testFetchCategoriesOverTree()
    {
        $api = $this->getShopApi();
        $tree = $api->fetchCategoryTree();
        $categories = $tree->getCategories(); 
        $ids = array();
       
        foreach ($categories as $category) {
            $this->assertInstanceOf('Collins\ShopApi\Model\Category', $category);            
            
            if (count($ids) < 5) {
                $ids[] = $category->getId();
            }
        }    
        
        return $ids;
    }
    
    /**
     * @depends testFetchCategoriesOverTree
     * @group live
     */
    public function testFetchCategoriesByIds($ids)
    {
        $api = $this->getShopApi();
        $categories = $api->fetchCategoriesByIds($ids);
               
        $this->assertCount($categories->count(), $ids);
    }

}
