<?php

namespace AboutYou\SDK\Test\Functional\ProductSearch;

use AboutYou\SDK\Model\ProductSearchResult;
use AboutYou\SDK\Test\Functional\AbstractShopApiTest;

class ProductSearchRealLifeTest extends AbstractShopApiTest
{
    protected $facetsResultPath = null;

    public function testProductSearchPriceRange()
    {
        $shopApi = $this->getShopApiWithResultFile('product_search-20140414.json');

        // get all available products
        $productSearchResult = $shopApi->fetchProductSearch($shopApi->getProductSearchCriteria('12345'));
        $priceRanges = $productSearchResult->getPriceRanges();
        $maxPrice = $productSearchResult->getMaxPrice();
        $this->assertEquals(0, $maxPrice);
        $this->assertInternalType('array', $priceRanges);
        $this->assertCount(6, $priceRanges);

        foreach ($priceRanges as $priceRange) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\ProductSearchResult\\PriceRange', $priceRange);
            $this->assertEquals(0, $priceRange->getMax());
        }
    }

    public function testProductSearchRawFacets()
    {
        $shopApi = $this->getShopApiWithResultFiles(array(
            'product_search-20140414-2.json'
        ));

        // get all available products
        $productSearchResult = $shopApi->fetchProductSearch($shopApi->getProductSearchCriteria('12345'));
        $facets = $productSearchResult->getRawFacets();

        $this->assertInstanceOf('\stdClass', $facets);

        foreach ($facets as $facet) {
            $this->assertInstanceOf('\stdClass', $facet);
        }
    }

    /***************************************************/

    protected function getJsonStringFromFile($filepath)
    {
        if (strpos($filepath, '/') !== 0) {
            $filepath = __DIR__.'/testData/' . $filepath;
        }
        $jsonString = file_get_contents($filepath);

        return $jsonString;
    }
}