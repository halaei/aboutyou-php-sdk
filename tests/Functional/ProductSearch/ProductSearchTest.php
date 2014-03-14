<?php

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi\Criteria\ProductSearchCriteria;
use Collins\ShopApi\Model\Product;
use Collins\ShopApi\Model\ProductSearchResult;

class ProductSearchTestAbstract extends AbstractShopApiTest
{
    public function testProductSearch()
    {
        $shopApi = $this->getShopApiWithResultFile('product_search.json');

        // get all available products
        $productSearchResult = $shopApi->fetchProductSearch($shopApi->getProductSearchCriteria('12345'));
        $this->checkProductSearchResult($productSearchResult);
    }

    public function testProductSearchSort()
    {
        $shopApi = $this->getShopApiWithResultFile('product_search.json');

        // search products and sort
        $criteria = $shopApi->getProductSearchCriteria('12345')
            ->sortBy(
                ProductSearchCriteria::SORT_TYPE_MOST_VIEWED
            )
        ;
        $productSearchResult = $shopApi->fetchProductSearch($criteria);
        $this->checkProductSearchResult($productSearchResult);

        $rawFacets = $productSearchResult->getRawFacets();
        $this->assertInstanceOf('\stdClass', $rawFacets);
        $this->assertObjectHasAttribute("0", $rawFacets);
        $brandFacets = $rawFacets->{"0"};
        $this->assertInstanceOf('\stdClass', $brandFacets);
        $this->assertObjectHasAttribute('_type', $brandFacets);
        $this->assertObjectHasAttribute('total', $brandFacets);
        $this->assertObjectHasAttribute('terms', $brandFacets);
        $this->assertObjectHasAttribute('other', $brandFacets);
        $this->assertObjectHasAttribute('missing', $brandFacets);
    }

    /**
     * @see tests/unit/ShopApi/ProductSearchFilterTest.php
     */
    public function testProductSearchFilterObject()
    {
        // This is the imported part of this test!!
        $expectedRequestBody = '["categories": [123]]';

        $shopApi = $this->getShopApiWithResult($this->getDummyResult(), $expectedRequestBody);

        // search products by filter
        $criteria = $shopApi->getProductSearchCriteria('12345');
        $criteria->filterByCategoryIds(array(
            123
        ));
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
        $criteria = $shopApi->getProductSearchCriteria('12345')
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

    protected function getDummyResult()
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

        return $dummyResult;
    }

    protected function getJsonStringFromFile($filepath)
    {
        if (strpos($filepath, '/') !== 0) {
            $filepath = __DIR__.'/testData/' . $filepath;
        }
        $jsonString = file_get_contents($filepath);

        return $jsonString;
    }
}