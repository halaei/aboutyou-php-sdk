<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class CategoryTest extends AbstractShopApiTest
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFetchCategoryTreeWithDepthGreaterThan10()
    {
        $this->markTestIncomplete();
        $shopApi = $this->getShopApiWithResult('category-tree-v2.json');

        $shopApi->fetchCategoryTree(1000);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFetchCategoryTreeWithDepthLessThanMiuns1()
    {
        $this->markTestIncomplete();
        $shopApi = $this->getShopApiWithResult('category-tree-v2.json');
        
        $shopApi->fetchCategoryTree(-1000);
    }         

    /**
     *
     */
    public function testBreadcrumb()
    {
        $shopApi = $this->getShopApiWithResult(''); // Init DefaultModelFactory
        $categoryManager = $shopApi->getResultFactory()->getCategoryManager();
        $json = $this->getJsonObjectFromFile('category-tree-v2.json');
        $categoryManager->parseJson($json[0]->category_tree, $shopApi->getResultFactory());
        $category = \Collins\ShopApi\Model\Category::createFromJson(reset($json[0]->category_tree->ids), $categoryManager);

        $breadcrumb = $category->getBreadcrumb();
        $this->assertCount(1, $breadcrumb);
        $this->assertEquals(74415, $breadcrumb[0]->getId());

        $subcategories = $category->getSubCategories();
        $breadcrumb = $subcategories[0]->getBreadcrumb();
        $this->assertCount(2, $breadcrumb);
        $this->assertEquals(74415, $breadcrumb[0]->getId());
        $this->assertEquals(74417, $breadcrumb[1]->getId());
    }
}
