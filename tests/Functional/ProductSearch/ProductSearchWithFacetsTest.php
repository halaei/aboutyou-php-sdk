<?php

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi\Criteria\ProductSearchCriteria;
use Collins\ShopApi\Model\Product;
use Collins\ShopApi\Model\ProductSearchResult;

class ProductSearchWithFacetsTestAbstract extends AbstractShopApiTest
{
    public function testProductSearchWithSaleResult()
    {
        $shopApi = $this->getShopApiWithResultFile('result-product-search-with-facets.json');

        $productSearchResult = $shopApi->fetchProductSearch($shopApi->getProductSearchCriteria('12345'));

        $saleFacet = $productSearchResult->getSaleCounts();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\ProductSearchResult\\SaleCounts', $saleFacet);
        $this->assertEquals(25303, $saleFacet->getProductCountTotal());
        $this->assertEquals(5261, $saleFacet->getProductCountInSale());
        $this->assertEquals(20042, $saleFacet->getProductCountNotInSale());
    }

    public function testProductSearchWithPriceRangeResult()
    {
        $shopApi = $this->getShopApiWithResultFile('result-product-search-with-facets.json');

        // get all available products
        $productSearchResult = $shopApi->fetchProductSearch($shopApi->getProductSearchCriteria('12345'));
        $priceRanges = $productSearchResult->getPriceRanges();
        $this->assertInternalType('array', $priceRanges);
        $this->assertCount(6, $priceRanges);

        $this->assertEquals(25138, $priceRanges[0]->getProductCount());
        $this->assertEquals(0, $priceRanges[0]->getFrom());
        $this->assertEquals(20000, $priceRanges[0]->getTo());
        $this->assertEquals(399, $priceRanges[0]->getMin());
        $this->assertEquals(19999, $priceRanges[0]->getMax());
        $this->assertEquals(5328, $priceRanges[0]->getMean());
        $this->assertEquals(133930606, $priceRanges[0]->getSum());

        $this->assertEquals(163, $priceRanges[1]->getProductCount());
        $this->assertEquals(20000, $priceRanges[1]->getFrom());
        $this->assertEquals(50000, $priceRanges[1]->getTo());
        $this->assertEquals(20000, $priceRanges[1]->getMin());
        $this->assertEquals(39995, $priceRanges[1]->getMax());
        $this->assertEquals(25199, $priceRanges[1]->getMean());
        $this->assertEquals(4107552, $priceRanges[1]->getSum());

        $this->assertEquals(0, $priceRanges[5]->getProductCount());
        $this->assertEquals(500000, $priceRanges[5]->getFrom());
        $this->assertEquals(null, $priceRanges[5]->getTo());
        $this->assertEquals(null, $priceRanges[5]->getMin());
        $this->assertEquals(null, $priceRanges[5]->getMax());
        $this->assertEquals(0, $priceRanges[5]->getMean());
        $this->assertEquals(0, $priceRanges[5]->getSum());

        $this->assertEquals(399, $productSearchResult->getMinPrice());
        $this->assertEquals(59900, $priceRanges[2]->getMax());
        $this->assertEquals(59900, $productSearchResult->getMaxPrice());
    }

    public function testProductSearchWithFacetResult()
    {
        $this->markTestIncomplete('Is not implemented yet');
    }

    public function testProductSearchWithCategoriesResult()
    {
        $this->markTestIncomplete('Is not implemented yet');
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