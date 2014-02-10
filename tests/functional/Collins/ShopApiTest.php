<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\Test\Functional;

use Collins\ShopApi;

class ShopApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Collins\ShopApi
     */
    private $api = null;

    /**
     *
     */
    public function setUp()
    {
        $this->api = new ShopApi('106', '7898aaf62cccbeb7210660b86ac80847');
    }

    /**
     *
     */
    public function testFetchProducts()
    {
        $productIds = array(123, 456);
        $products = $this->api->fetchProductsByIds($productIds);
        $this->checkProduct($products[123]);
        $this->checkProduct($products[456]);
    }

    /**
     *
     */
    public function testSearchProducts()
    {
        // get all available products
        $products = $this->api->searchProducts();
        $this->checkProductList($products);

        // search products by filter
        $filter = array(
            'categoryId' => 123
        );
        $products = $this->api->searchProducts($filter);
        $this->checkProductList($products);

        // search products and sort
        $sorting = array('name', ShopApi::SORT_ASC);
        $products = $this->api->searchProducts(null, $sorting);
        $this->checkProductList($products);

        // search products with limit
        $pagination = array(
            'pageSize' => 20,
            'page' => 1,
        );
        // or:
        $pagination = array(
            'limit' => 20,
            'offset' => 21,
        );
        $products = $this->api->searchProducts(null, null, $pagination);
        $this->checkProductList($products);
    }

    /**
     *
     */
    public function testFetchCategoryTree()
    {
        $depth = 1;
        $categories = $this->api->fetchCategoryTree($depth);
//        var_export($categories);

        foreach ($categories as $category) {
            $this->checkCategory($category);

            foreach ($category->sub_categories as $subCategory) {
                $this->checkCategory($subCategory);
                $this->assertEmpty($subCategory->sub_categories);
            }
        }
    }

    /**
     *
     */
    public function testFetchParentCategories()
    {
        $categoryId = 123;
        $categories = $this->api->fetchParentCategories($categoryId);

        $this->assertTrue(is_array($categories));
        foreach ($categories as $category) {
            $this->checkCategory($category);
        }
    }

    /**
     *
     */
    private function checkProduct($product)
    {
        $this->assertObjectHasAttribute('id', $product);
        $this->assertObjectHasAttribute('name', $product);
        //TODO: check if this is a product
    }

    /**
     *
     */
    private function checkProductList($products)
    {
        $this->assertTrue(is_array($products));
        foreach ($products as $product) {
            $this->checkProduct($product);
        }
    }

    /**
     *
     */
    private function checkCategory($category)
    {
//        $this->assertObjectHasAttribute('id', $category);
//        $this->assertObjectHasAttribute('name', $category);
//        $this->assertObjectHasAttribute('active', $category);
        $this->assertNotNull($category->id);
        $this->assertNotNull($category->name);
        $this->assertNotNull($category->active);
        //TODO: check if this is a category
    }
}
