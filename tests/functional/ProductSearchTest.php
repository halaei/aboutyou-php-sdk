<?php

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi\Criteria\ProductSearchCriteria;
use Collins\ShopApi\Model\Product;
use Collins\ShopApi\Model\ProductSearchResult;

class ProductSearchTest extends ShopApiTest
{
    public function testProductSearch()
    {
        $shopApi = $this->getShopApiWithResultFile('product_search.json');

        // get all available products
        $productSearchResult = $shopApi->fetchProductSearch($shopApi->getProductSearchCriteria('1234'));
        $this->checkProductSearchResult($productSearchResult);
    }

    public function testProductSearchSort()
    {
        $shopApi = $this->getShopApiWithResultFile('product_search.json');

        // search products and sort
        $criteria = $shopApi->getProductSearchCriteria('1234')
            ->sortBy(
                ProductSearchCriteria::SORT_TYPE_MOST_VIEWED
            )
        ;
        $products = $shopApi->fetchProductSearch($criteria);
        $this->checkProductSearchResult($products);
    }

    /**
     * @see tests/unit/ShopApi/ProductSearchFilterTest.php
     */
    public function testProductSearchFilterObject()
    {
        $dummyResult = <<<EOS
[
    {
        "product_search": {
            "product_count": 1234,
            "pageHash": "d136109b-abd8-4d1c-99ac-4a621f3adb0e",
            "facets": {},
            "products": []
        }
    }
]
EOS;
        // This is the imported part of this test!!
        $expectedRequestBody = '["categories": [123]]';

        $shopApi = $this->getShopApiWithResult($dummyResult, $expectedRequestBody);

        // search products by filter
        $criteria = $shopApi->getProductSearchCriteria('1234');
        $criteria->addCategories([
            123
        ]);
        $products = $shopApi->fetchProductSearch($criteria);
        $this->checkProductSearchResult($products);
    }

    public function testProductSearchPagination()
    {
        $shopApi = $this->getShopApiWithResultFile('product_search.json');

        $pagination = array(
            'limit' => 20,
            'offset' => 21,
        );
        $criteria = $shopApi->getProductSearchCriteria('1234')
            ->setLimit($pagination['limit'], $pagination['offset'])
        ;
        $products = $shopApi->fetchProductSearch($criteria);
        $this->checkProductSearchResult($products);
    }

    /***************************************************/

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