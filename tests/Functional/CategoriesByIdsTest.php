<?php
namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

/**
 *
 */
class CategoriesByIdsTestAbstract extends AbstractShopApiTest
{
    public function testFetchCategories()
    {
        $categoryIds = array(16080, 16138, 123);

        $shopApi = $this->getShopApiWithResultFile('category.json');

        $categoriesResult = $shopApi->fetchCategoriesByIds($categoryIds);
        $categories = $categoriesResult->getCategories();
        $this->assertCount(2, $categories);

        $category = $categories[16080];
        $this->assertInstanceOf('\Collins\ShopApi\Model\Category', $category);
        $this->assertEquals(16080, $category->getId());
        $this->assertEquals('Blusen & Tuniken', $category->getName());
        $this->assertTrue($category->isActive());

        $category = $categories[16138];
        $this->assertInstanceOf('\Collins\ShopApi\Model\Category', $category);
        $this->assertEquals(16138, $category->getId());
        $this->assertEquals('Minikleider', $category->getName());
        $this->assertTrue($category->isActive());

        $notFound = $categoriesResult->getCategoriesNotFound();
        $this->assertEquals(123, $notFound[0]);

        return $categoriesResult;
    }

    /**
     * @depends testFetchCategories
     */
    public function testProductResultIteratorInterface($categoriesResult)
    {
        foreach ($categoriesResult as $category) {
            $this->assertInstanceOf('\Collins\ShopApi\Model\Category', $category);
        }
    }

    /**
     * @depends testFetchCategories
     */
    public function testProductResultArrayAccessInterface($categoriesResult)
    {
        $this->assertInstanceOf('\Collins\ShopApi\Model\Category', $categoriesResult[16080]);
        $this->assertInstanceOf('\Collins\ShopApi\Model\Category', $categoriesResult[16138]);
    }

    /**
     * @depends testFetchCategories
     */
    public function testProductResultCountableInterface($categoriesResult)
    {
        $this->assertCount(2, $categoriesResult);
    }
}
