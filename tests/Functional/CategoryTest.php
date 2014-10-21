<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Functional;

use \AY;

class CategoryTest extends AbstractShopApiTest
{
    /**
     *
     */
    public function testBreadcrumb()
    {
        $shopApi = $this->getShopApiWithResult(''); // Init DefaultModelFactory
        $categoryManager = $shopApi->getResultFactory()->getCategoryManager();
        $json = $this->getJsonObjectFromFile('category-tree-v2.json');
        $categoryManager->parseJson($json[0]->category_tree, $shopApi->getResultFactory());
        $category = \AboutYou\SDK\Model\Category::createFromJson(reset($json[0]->category_tree->ids), $categoryManager);

        $breadcrumb = $category->getBreadcrumb();
        $this->assertCount(1, $breadcrumb);
        $this->assertEquals(74415, reset($breadcrumb)->getId());

        $subcategories = $category->getSubCategories();
        $breadcrumb = reset($subcategories)->getBreadcrumb();
        $this->assertCount(2, $breadcrumb);
        $category = array_shift($breadcrumb);
        $this->assertEquals(74415, $category->getId());
        $category = array_shift($breadcrumb);
        $this->assertEquals(74417, $category->getId());
    }
}
