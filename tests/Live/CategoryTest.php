<?php

namespace Collins\ShopApi\Test\Live;

use Collins\ShopApi;

/**
 * @group live
 */
class CategoryTest extends ShopApi\Test\Live\AbstractShopApiLiveTest
{

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFetchCategoriesByIdWithStrings()
    {
        $api = $this->getShopApi();
        $api->fetchCategoriesByIds(';!');
    }
    
    public function testFetchCategoriesOverTree()
    {
        $api = $this->getShopApi();
        $tree = $api->fetchCategoryTree();
        $categories = $tree->getCategories(false);
        $ids = array();

        $this->assertGreaterThan(0, count($categories), 'please configure at least one category for the app (id: '.$api->getAppId().')');
       
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
     */
    public function testFetchCategoriesByIds($ids)
    {
        $api = $this->getShopApi();
        $categories = $api->fetchCategoriesByIds($ids);
               
        $this->assertCount($categories->count(), $ids);
    }
}
