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
     * @var $category \Collins\ShopApi\Model\Category
     */
    protected $category;

    public function setUp()
    {
        $shopApi = $this->getShopApiWithResult(''); // Init DefaultModelFactory
        $json = json_decode(file_get_contents(__DIR__ . '/testData/category-tree.json'));
        $this->category = \Collins\ShopApi\Model\Category::createFromJson($json[0]->category_tree[1], $shopApi->getResultFactory());
    }

    /**
     *
     */
    public function testBreadcrumb()
    {
        $breadcrumb = $this->category->getBreadcrumb();
//        echo '<pre>', __LINE__, ') ', __METHOD__, ': <b>$breadcrumb</b>=', var_export($breadcrumb), '</pre>', PHP_EOL;
        $this->assertCount(1, $breadcrumb);
        $this->assertEquals(200, $breadcrumb[0]->getId());

        $subcategories = $this->category->getSubCategories();
        $breadcrumb = $subcategories[0]->getBreadcrumb();
        $this->assertCount(2, $breadcrumb);
        $this->assertEquals(200, $breadcrumb[0]->getId());
        $this->assertEquals(210, $breadcrumb[1]->getId());
    }
}
