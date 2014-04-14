<?php

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi\Model\ProductSearchResult;

class ProductSearchRealLifeTest extends AbstractShopApiTest
{
    public function testProductSearch()
    {
        $shopApi = $this->getShopApiWithResultFile('product_search_20140414.json');

        // get all available products
        $productSearchResult = $shopApi->fetchProductSearch($shopApi->getProductSearchCriteria('12345'));
        $priceRanges = $productSearchResult->getPriceRanges();
        $maxPrice = $productSearchResult->getMaxPrice();
        $this->assertEquals(0, $maxPrice);
        $this->assertInternalType('array', $priceRanges);
        $this->assertCount(6, $priceRanges);

        foreach ($priceRanges as $priceRange) {
            $this->assertInstanceOf('Collins\\ShopApi\\Model\\ProductSearchResult\\PriceRange', $priceRange);
            $this->assertEquals(0, $priceRange->getMax());
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