<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class ShopApiCategoryTreeTest extends ShopApiTest
{
     /**
     *
     */
    public function testFetchCategoryTree()
    {
        $depth = 1;

        $jsonString = file_get_contents(__DIR__.'/testData/app-category-tree.json');
        $shopApi = $this->getShopApiWithResult($jsonString);

        $categoryTree = $shopApi->fetchCategoryTree($depth);

        foreach ($categoryTree->getCategories() as $category) {
            $this->checkCategory($category);

            foreach ($category->getSubCategories() as $subCategory) {
                $this->checkCategory($subCategory);
                $this->assertEquals($category, $subCategory->getParent());
                $this->assertEmpty($subCategory->getSubCategories());
            }
        }
    }


    /**
     *
     */
    private function checkCategory($category)
    {
        $this->assertObjectHasAttribute('id', $category);
        $this->assertObjectHasAttribute('name', $category);
        $this->assertObjectHasAttribute('isActive', $category);
        $this->assertNotNull($category->id);
        $this->assertNotNull($category->name);
        $this->assertNotNull($category->isActive);
        //TODO: check if this is a category
    }
}
