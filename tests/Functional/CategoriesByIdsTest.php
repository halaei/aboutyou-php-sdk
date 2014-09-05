<?php
namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class CategoriesByIdsTest extends AbstractShopApiTest
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFetchCategoriesWithWrongIds()
    {
        $shopApi = $this->getShopApiWithResultFile('category-tree-v2.json');

        $categoriesResult = $shopApi->fetchCategoriesByIds(array('kfd', false,  null, 212312, ));        
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFetchCategoriesWithStringIdsAndFalse()
    {
        $shopApi = $this->getShopApiWithResultFile('category-tree-v2.json');

        $categoriesResult = $shopApi->fetchCategoriesByIds(array('1', '2', false));        
    }   
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFetchCategoriesWithNegativeIds()
    {
        $shopApi = $this->getShopApiWithResultFile('category-tree-v2.json');

        $categoriesResult = $shopApi->fetchCategoriesByIds(array(-1, -2, -4));        
    }     
    
    public function testFetchCategoriesWithStringIds()
    {
        $categoryIds = array('16080', '16138', '123');
        $shopApi = $this->getShopApiWithResultFile('category.json');

        $categoriesResult = $shopApi->fetchCategoriesByIds($categoryIds);

        $categories = $categoriesResult->getCategories();
        
        $this->assertCount(2, $categories);        
    }     
    
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
