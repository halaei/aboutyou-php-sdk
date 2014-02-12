<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class CategoryTest extends ShopApiTest
{
    /**
     * @var $category \Collins\ShopApi\Model\Category
     */
    protected $category;

    public function setUp()
    {
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
        $this->assertEquals(33, $breadcrumb[0]->id);

        $breadcrumb = $this->category->getSubCategories()[0]->getBreadcrumb();
        $this->assertCount(2, $breadcrumb);
        $this->assertEquals(33, $breadcrumb[0]->id);
        $this->assertEquals(22, $breadcrumb[1]->id);
    }
}
