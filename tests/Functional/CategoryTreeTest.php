<?php
namespace AboutYou\SDK\Test\Functional;

use AboutYou\SDK\Model\Category;

class CategoryTreeTest extends AbstractAYTest
{
    public function testFetchCategoryTree()
    {
        $ay = $this->getAYWithResultFile('category-tree-v2.json');
        $categoryTreeResult = $ay->fetchCategoryTree();
        $categories = $categoryTreeResult->getCategories();
        $this->assertCount(2, $categories);

        foreach ($categories as $category) {
            $this->assertInstanceOf('\AboutYou\SDK\Model\Category', $category);
            $this->assertTrue($category->isActive());
            $subCategories = $category->getSubCategories();
            $this->assertCount(3, $subCategories);
            $this->assertEquals('Shirts', array_shift($subCategories)->getName());
            $this->assertEquals('Jeans',  array_shift($subCategories)->getName());
            $this->assertEquals('Schuhe', array_shift($subCategories)->getName());
        }

        $this->assertArrayHasKey(74415, $categories);
        $this->assertArrayHasKey(74416, $categories);
        $this->assertArrayNotHasKey(74423, $categories);

        $category = array_shift($categories);
        $this->assertEquals(74415,    $category->getId());
        $this->assertEquals('Frauen', $category->getName());

        $category = array_shift($categories);
        $this->assertEquals(74416,    $category->getId());
        $this->assertEquals('Männer', $category->getName());
        $this->assertCount(3, $category->getSubCategories());


        $categories = $categoryTreeResult->getCategories(Category::ALL);
        $this->assertCount(3, $categories);
        $this->assertArrayHasKey(74415, $categories);
        $this->assertArrayHasKey(74416, $categories);
        $this->assertArrayHasKey(74423, $categories);

        $category = array_pop($categories);
        $this->assertEquals(74423,    $category->getId());
        $this->assertEquals('Landing Page', $category->getName());
        $this->assertCount(0, $category->getSubCategories());

        return $categoryTreeResult;
    }

    /**
     * @depends testFetchCategoryTree
     */
    public function testProductResultIteratorInterface($categoryTreeResult)
    {
        foreach ($categoryTreeResult as $category) {
            $this->assertInstanceOf('\AboutYou\SDK\Model\Category', $category);
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
