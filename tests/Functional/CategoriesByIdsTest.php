<?php
namespace AboutYou\SDK\Test\Functional;

use \AY;

class CategoriesByIdsTest extends AbstractAYTest
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFetchCategoriesWithWrongIds()
    {
        $ay = $this->getAYWithResultFile('category-tree-v2.json');

        $categoriesResult = $ay->fetchCategoriesByIds(array('kfd', false,  null, 212312, ));
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFetchCategoriesWithStringIdsAndFalse()
    {
        $ay = $this->getAYWithResultFile('category-tree-v2.json');

        $categoriesResult = $ay->fetchCategoriesByIds(array('1', '2', false));
    }   
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFetchCategoriesWithNegativeIds()
    {
        $ay = $this->getAYWithResultFile('category-tree-v2.json');

        $categoriesResult = $ay->fetchCategoriesByIds(array(-1, -2, -4));
    }     
    
    public function testFetchCategoriesWithStringIds()
    {
        $categoryIds = array(74415, 74420, 123);
        $ay = $this->getAYWithResultFile('category-tree-v2.json');

        $categoriesResult = $ay->fetchCategoriesByIds($categoryIds);

        $categories = $categoriesResult->getCategories();
        
        $this->assertCount(2, $categories);        
    }     
    
    public function testFetchCategories()
    {
        $categoryIds = array(74415, 74420, 123);

        $ay = $this->getAYWithResultFile('category.json');

        $categoriesResult = $ay->fetchCategoriesByIds($categoryIds);
        $categories = $categoriesResult->getCategories();
        $this->assertCount(2, $categories);

        $category = $categories[74415];
        $this->assertInstanceOf('\AboutYou\SDK\Model\Category', $category);
        $this->assertEquals(74415, $category->getId());
        $this->assertEquals('Frauen', $category->getName());
        $this->assertTrue($category->isActive());

        $category = $categories[74420];
        $this->assertInstanceOf('\AboutYou\SDK\Model\Category', $category);
        $this->assertEquals(74420, $category->getId());
        $this->assertEquals('Jeans', $category->getName());
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
            $this->assertInstanceOf('\AboutYou\SDK\Model\Category', $category);
        }
    }

    /**
     * @depends testFetchCategories
     */
    public function testProductResultArrayAccessInterface($categoriesResult)
    {
        $this->assertInstanceOf('\AboutYou\SDK\Model\Category', $categoriesResult[74415]);
        $this->assertInstanceOf('\AboutYou\SDK\Model\Category', $categoriesResult[74420]);
    }

    /**
     * @depends testFetchCategories
     */
    public function testProductResultCountableInterface($categoriesResult)
    {
        $this->assertCount(2, $categoriesResult);
    }
}
