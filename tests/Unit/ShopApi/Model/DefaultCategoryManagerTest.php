<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Unit\Model;

use Collins\ShopApi\Model\Category;
use Collins\ShopApi\Model\CategoryManager\DefaultCategoryManager;

class DefaultCategoryManagerTest extends AbstractModelTest
{
    public function testParseJson()
    {
        $factory = $this->getModelFactory();

        $categoryManager = new DefaultCategoryManager();
        $factory->setCategoryManager($categoryManager);
        $jsonObject = $this->getJsonObject('category-tree-v2.json');
        $categoryManager->parseJson($jsonObject, $factory);

        return $categoryManager;
    }

    /**
     * @depends testParseJson
     */
    public function testGetCategory(DefaultCategoryManager $categoryManager)
    {
        $unknownId = 1;
        $category = $categoryManager->getCategory($unknownId);
        $this->assertNull($category);

        $knownId = 74415;
        $category = $categoryManager->getCategory($knownId);
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Category', $category);
        $this->assertEquals($knownId, $category->getId());
    }

    /**
     * @depends testParseJson
     */
    public function testGetCategories(DefaultCategoryManager $categoryManager)
    {
        $unknownId = 1;
        $categories = $categoryManager->getCategories(array($unknownId));
        $this->assertCount(0, $categories);

        $knownId = 74415;
        $categories = $categoryManager->getCategories(array($unknownId, $knownId));
        $this->assertCount(1, $categories);
        foreach ($categories as $category) {
            $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Category', $category);
        }
        $this->checkMainCategory($category);
    }

    /**
     * @depends testParseJson
     */
    public function testGetCategoryTree(DefaultCategoryManager $categoryManager)
    {
        $categories = $categoryManager->getCategoryTree();
        $this->assertCount(2, $categories);
        foreach ($categories as $category) {
            $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Category', $category);

            $this->checkMainCategory($category);
        }

        return $categories;
    }

    /**
     * @depends testParseJson
     */
    public function testGetSubCategories(DefaultCategoryManager $categoryManager)
    {
        $unknownId = 1;
        $subCategories = $categoryManager->getSubCategories($unknownId);
        $this->assertCount(0, $subCategories);

        $knownId = 74415;
        $subCategories = $categoryManager->getSubCategories($knownId);
        $this->assertCount(3, $subCategories);
        foreach ($subCategories as $subCategory) {
            $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Category', $subCategory);
        }
    }

    private function checkMainCategory(Category $category)
    {
        $subCategories = $category->getSubCategories();
        $this->assertCount(3, $subCategories);

        foreach ($subCategories as $subCategory) {
            $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Category', $subCategory);
            $this->assertEquals($category, $subCategory->getParent());
        }
    }

    /**
     * @param Category[] $categories
     *
     * @depends testGetCategoryTree
     */
    public function testCategoryTreeHierarchy($categories)
    {
        foreach ($categories as $category) {
            foreach ($category->getSubCategories() as $subCategory) {
                $this->assertEquals($category, $subCategory->getParent());
            }
        }

        $female  = $categories[0];
        $this->assertEquals('Frauen', $female->getName());
        $femaleCats = $female->getSubCategories();
        $this->assertEquals('Shirts', $femaleCats[0]->getName());
        $this->assertEquals(74417, $femaleCats[0]->getId());
        $this->assertEquals('Jeans', $femaleCats[1]->getName());
        $this->assertEquals(74419, $femaleCats[1]->getId());
        $this->assertEquals('Schuhe', $femaleCats[2]->getName());
        $this->assertEquals(74421, $femaleCats[2]->getId());

        $male = $categories[1];
        $this->assertEquals('Männer', $male->getName());
        $maleCats = $male->getSubCategories();
        $this->assertEquals('Shirts', $maleCats[0]->getName());
        $this->assertEquals(74418, $maleCats[0]->getId());
        $this->assertEquals('Jeans', $maleCats[1]->getName());
        $this->assertEquals(74420, $maleCats[1]->getId());
        $this->assertEquals('Schuhe', $maleCats[2]->getName());
        $this->assertEquals(74422, $maleCats[2]->getId());
    }
}
 