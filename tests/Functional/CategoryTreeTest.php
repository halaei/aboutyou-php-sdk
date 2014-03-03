<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;
use Collins\ShopApi\Model\Category;

class CategoryTreeTestAbstract extends AbstractShopApiTest
{
     /**
     *
     */
    public function testFetchCategoryTree()
    {
        $depth = 1;

        $shopApi = $this->getShopApiWithResultFile('category-tree.json');

        $categoryTree = $shopApi->fetchCategoryTree($depth);

        $this->assertCount(2, $categoryTree->getCategories(Category::ALL));
        $this->assertCount(1, $categoryTree->getCategories(Category::ACTIVE_ONLY));
        $this->assertCount(1, $categoryTree->getCategories());
        $this->assertEquals(2, count($categoryTree));

        $subCategory = $categoryTree->getCategories()[0];
        $this->assertCount(2, $subCategory->getSubCategories(Category::ALL));
        $this->assertCount(1, $subCategory->getSubCategories(Category::ACTIVE_ONLY));
        $this->assertCount(1, $subCategory->getSubCategories());

        foreach ($categoryTree->getCategories(Category::ALL) as $category) {
            $this->checkCategory($category);

            foreach ($category->getSubCategories(Category::ALL) as $subCategory) {
                $this->checkCategory($subCategory);
                $this->assertEquals($category, $subCategory->getParent());
                $this->assertEmpty($subCategory->getSubCategories());
            }
        }

        foreach ($categoryTree->getCategories() as $category) {
            $this->checkCategory($category);
            $this->assertTrue($category->isActive());

            foreach ($category->getSubCategories() as $subCategory) {
                $this->checkCategory($subCategory);
                $this->assertTrue($subCategory->isActive());
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
            $this->assertTrue($category->isActive());
        }
    }

    /**
     * @param Category $category
     */
    private function checkCategory(Category $category)
    {
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Category', $category);
        $this->assertNotNull($category->getId());
        $this->assertNotNull($category->getName());
        $this->assertNotNull($category->isActive());
    }
}
