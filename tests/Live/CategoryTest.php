<?php

namespace AboutYou\SDK\Test\Live;

/**
 * @group live
 */
class CategoryTest extends AbstractShopApiLiveTest
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
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Category', $category);
            
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
