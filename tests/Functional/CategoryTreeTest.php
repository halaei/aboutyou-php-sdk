<?php
namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class CategoryTreeTest extends AbstractShopApiTest
{
    public function testFetchCategoryTree()
    {
        $shopApi = $this->getShopApiWithResultFile('category-tree-v2.json');
        $categoryTreeResult = $shopApi->fetchCategoryTree();
        $categories = $categoryTreeResult->getCategories();
        $this->assertCount(2, $categories);

        foreach ($categories as $category) {
            $this->assertInstanceOf('\Collins\ShopApi\Model\Category', $category);
            $this->assertTrue($category->isActive());
            $subCategories = $category->getSubCategories();
            $this->assertCount(3, $subCategories);
            $this->assertEquals('Shirts', $subCategories[0]->getName());
            $this->assertEquals('Jeans',  $subCategories[1]->getName());
            $this->assertEquals('Schuhe', $subCategories[2]->getName());
        }

        $this->assertEquals(74415,    $categories[0]->getId());
        $this->assertEquals('Frauen', $categories[0]->getName());

        $this->assertEquals(74416,    $categories[1]->getId());
        $this->assertEquals('Männer', $categories[1]->getName());
        $this->assertCount(3, $categories[1]->getSubCategories());

        $this->assertArrayNotHasKey(2, $categories);


        $categories = $categoryTreeResult->getCategories(false);
        $this->assertCount(3, $categories);

        $this->assertEquals(74423,    $categories[2]->getId());
        $this->assertEquals('Landing Page', $categories[2]->getName());
        $this->assertCount(0, $categories[2]->getSubCategories());

        return $categoryTreeResult;
    }

    /**
     * @depends testFetchCategoryTree
     */
    public function testProductResultIteratorInterface($categoryTreeResult)
    {
        foreach ($categoryTreeResult as $category) {
            $this->assertInstanceOf('\Collins\ShopApi\Model\Category', $category);
        }
    }

    /**
     * @depends testFetchCategoryTree
     */
    public function testProductResultCountableInterface($categoryTreeResult)
    {
        $this->assertCount(2, $categoryTreeResult);
    }
}
