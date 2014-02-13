<?php

namespace Collins\ShopApi\Test\Functional;

class ProductSearchTest extends ShopApiTest
{
    public function testProductSearch()
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