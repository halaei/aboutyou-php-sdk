<?php

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi\Model\Product;
use Collins\ShopApi\Model\ProductSearchResult;

class ProductSearchTest extends ShopApiTest
{
    public function testProductSearch()
    {
        $this->markTestIncomplete();

        $shopApi = $this->getShopApiWithResultFile('product_search.json');

        // get all available products
        $productSearchResult = $shopApi->fetchProductSearch('1234');
        $this->checkProductSearchResult($productSearchResult);
    }

    public function testProductSearchFilter()
    {
//        $this->markTestIncomplete();

        $shopApi = $this->getShopApiWithResultFile('product_search.json');

        // search products by filter
        $filter = array(
            'categoryId' => 123
        );
        $products = $shopApi->fetchProductSearch('1234', $filter);
        $this->checkProductSearchResult($products);

//        // search products and sort
//        $sorting = array('name', ShopApi::SORT_ASC);
//        $products = $shopApi->fetchSearchProducts(null, $sorting);
//        $this->checkProductList($products);
    }

    public function testProductSearchPagination()
    {
        $this->markTestIncomplete();

        $shopApi = $this->getShopApiWithResultFile('product_search.json');

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
        $products = $shopApi->fetchSearchProducts(null, null, $pagination);
    }

    /**
     *
     */
    private function checkProduct(Product $product)
    {
        $this->assertObjectHasAttribute('id', $product);
        $this->assertObjectHasAttribute('name', $product);
    }

    private function checkProductSearchResult(ProductSearchResult $products)
    {
        $this->assertEquals(1234, $products->getProductCount());

        foreach ($products as $product) {
            $this->checkProduct($product);
        }
    }
}