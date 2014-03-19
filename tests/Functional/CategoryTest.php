<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class CategoryTestAbstract extends AbstractShopApiTest
{
    /**
     * @var $category \Collins\ShopApi\Model\Category
     */
    protected $category;

    public function setUp()
    {
        $this->getShopApiWithResult(''); // Init DefaultModelFactory
        $json = json_decode(file_get_contents(__DIR__ . '/testData/category-tree.json'));
        $this->category = new \Collins\ShopApi\Model\Category($json[0]->category_tree[1]);
    }

    /**
     *
     */
    public function testBreadcrumb()
    {
        $breadcrumb = $this->category->getBreadcrumb();
        $this->assertCount(1, $breadcrumb);
        $this->assertEquals(200, $breadcrumb[0]->getId());

        $subcategories = $this->category->getSubCategories();
        $breadcrumb = $subcategories[0]->getBreadcrumb();
        $this->assertCount(2, $breadcrumb);
        $this->assertEquals(200, $breadcrumb[0]->getId());
        $this->assertEquals(210, $breadcrumb[1]->getId());
    }
}
