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
//        $factory = $this->getModelFactoryMock();
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
     * @depends testGetCategoryTree
     */
    public function testCategoryTreeHierarchy($categories)
    {
        $frauen  = $categories[74415];
        $maenner = $categories[74416];
    }
}
 