<?php

namespace AboutYou\SDK\Test\Functional\ProductSearch;

use AboutYou\SDK\Criteria\ProductSearchCriteria;
use AboutYou\SDK\Model\Product;
use AboutYou\SDK\Model\ProductSearchResult;
use AboutYou\SDK\Test\Functional\AbstractAYTest;

class ProductSearchTest extends AbstractAYTest
{
    protected $facetsResultPath = null;

    public function testProductSearch()
    {
        $ay = $this->getAYWithResultFileAndFacets(
            'product_search.json',
            'facet-result.json'
        );

        // get all available products
        $productSearchResult = $ay->fetchProductSearch($ay->getProductSearchCriteria('12345'));
        $this->checkProductSearchResult($productSearchResult);
    }

    public function testProductSearchSort()
    {
        $ay = $this->getAYWithResultFileAndFacets(
            'product_search.json',
            'facet-result.json'
        );

        // search products and sort
        $criteria = $ay->getProductSearchCriteria('12345')
            ->sortBy(
                ProductSearchCriteria::SORT_TYPE_MOST_VIEWED
            )
        ;
        $productSearchResult = $ay->fetchProductSearch($criteria);
        $this->checkProductSearchResult($productSearchResult);

        $rawFacets = $productSearchResult->getRawFacets();
        $this->assertInstanceOf('\stdClass', $rawFacets);
        $this->assertTrue(isset($rawFacets->{'0'}), 'rawFacets has no attribute "0"');
        $brandFacets = $rawFacets->{'0'};
        $this->assertInstanceOf('\stdClass', $brandFacets);
        $this->assertObjectHasAttribute('_type', $brandFacets);
        $this->assertObjectHasAttribute('total', $brandFacets);
        $this->assertObjectHasAttribute('terms', $brandFacets);
        $this->assertObjectHasAttribute('other', $brandFacets);
        $this->assertObjectHasAttribute('missing', $brandFacets);
    }

    /**
     * @see tests/unit/AboutYou/ProductSearchFilterTest.php
     */
    public function testProductSearchFilterObject()
    {
        // This is the imported part of this test!!
        $expectedRequestBody = '[{"product_search":{"session_id":"12345","filter":{"categories":[123]}}}]';

        $ay = $this->getAYWithResult($this->getDummyResult(), $expectedRequestBody);

        // search products by filter
        $criteria = $ay->getProductSearchCriteria('12345');
        $criteria->filterByCategoryIds(array(
            123
        ));
        $products = $ay->fetchProductSearch($criteria);
        $this->checkProductSearchResult($products);
    }

    public function testProductSearchPagination()
    {
        $ay = $this->getAYWithResultFileAndFacets(
            'product_search.json',
            'facet-result.json'
        );

        $pagination = array(
            'limit' => 20,
            'offset' => 21,
        );
        $criteria = $ay->getProductSearchCriteria('12345')
            ->setLimit($pagination['limit'], $pagination['offset'])
        ;
        $products = $ay->fetchProductSearch($criteria);
        $this->checkProductSearchResult($products);
    }

    public function testProductGetEmptyCategoryTree()
    {
        $ay = $this->getAYWithResultFileAndFacets(
            'product_search.json',
            'facet-result.json'
        );
        
        $pagination = array(
            'limit' => 20,
            'offset' => 21,
        );
        $criteria = $ay->getProductSearchCriteria('12345')
            ->setLimit($pagination['limit'], $pagination['offset'])
        ;
        $products = $ay->fetchProductSearch($criteria);
        
        $this->assertInternalType('array', $products->getCategoryTree());
    }
    
    public function testProductGetCategoryGetParent()
    {
        $ay = $this->getAYWithResultFile(
            'product-search-result-with-product-categories.json'
        );

        // get all available products
        $productSearchResult = $ay->fetchProductSearch($ay->getProductSearchCriteria('12345'));
        $products = $productSearchResult->getProducts();

        $product = $products[0];
        $category = $product->getCategory();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Category', $category);
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Category', $category->getParent());
        $this->assertNull($category->getParent()->getParent());
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

    protected function getJsonStringFromFile($filepath, $baseDir = __DIR__)
    {
        if (strpos($filepath, '/') !== 0) {
            $filepath = __DIR__.'/testData/' . $filepath;
        }
        $jsonString = file_get_contents($filepath);

        return $jsonString;
    }
}