<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class CategoryTreeTest extends ShopApiTest
{
     /**
     *
     */
    public function testFetchCategoryTree()
    {
        $depth = 1;

        $shopApi = $this->getShopApiWithResultFile('category-tree.json');

        $categoryTree = $shopApi->fetchCategoryTree($depth);

        foreach ($categoryTree->getCategories() as $category) {
            $this->checkCategory($category);

            foreach ($category->getSubCategories() as $subCategory) {
                $this->checkCategory($subCategory);
                $this->assertEquals($category, $subCategory->getParent());
                $this->assertEmpty($subCategory->getSubCategories());
            }
        }

        return $categoryTree;
    }

    /**
     * @depends testFetchCategoryTree
     */
    public function testCategoryTreeIterator($categoryTree)
    {
        $this->assertInstanceOf('\IteratorAggregate', $categoryTree);

        foreach ($categoryTree as $category) {
            $this->checkCategory($category);
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
