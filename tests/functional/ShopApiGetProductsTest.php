<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class ShopApiGetProductsTest extends ShopApiTest
{
    /**
     *
     */
    public function testFetchProducts()
    {
        $this->markTestIncomplete();

        $productIds = array(123, 456);

        $shopApi = $this->getShopApiWithResult('');

        $products = $shopApi->fetchProductsByIds($productIds);
        $this->checkProduct($products[123]);
        $this->checkProduct($products[456]);
    }

    /**
     *
     */
    public function testSearchProducts()
    {
        $this->markTestIncomplete();

        $shopApi = $this->getShopApiWithResult('');

        // get all available products
        $products = $shopApi->searchProducts();
        $this->checkProductList($products);

        // search products by filter
        $filter = array(
            'categoryId' => 123
        );
        $products = $shopApi->searchProducts($filter);
        $this->checkProductList($products);

        // search products and sort
        $sorting = array('name', ShopApi::SORT_ASC);
        $products = $shopApi->searchProducts(null, $sorting);
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
        $products = $shopApi->searchProducts(null, null, $pagination);
        $this->checkProductList($products);
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
}
