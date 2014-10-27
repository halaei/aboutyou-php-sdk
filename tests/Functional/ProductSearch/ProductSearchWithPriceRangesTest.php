<?php

namespace AboutYou\SDK\Test\Functional\ProductSearch;

use AboutYou\SDK\Model\ProductSearchResult;
use AboutYou\SDK\Test\Functional\AbstractAYTest;

class ProductSearchWithPriceRangesTest extends AbstractAYTest
{
    protected $facetsResultPath = null;

    public function testProductSearchWithSteadyPriceRangeResult()
    {
        $ay = $this->getAYWithResultFiles(array(
            'result-product-search-with-facets.json',
            'facet-result.json'
        ));

        // get all available products
        $productSearchResult = $ay->fetchProductSearch($ay->getProductSearchCriteria('12345'));
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

    public function testProductSearchWithDiscontinuesPriceRangeResult()
    {
        $ay = $this->getAYWithResultFiles(array(
            'result-product-search-with-discontinues-price-ranges.json',
            'facet-result.json'
        ));

        $productSearchResult = $ay->fetchProductSearch($ay->getProductSearchCriteria('12345'));
        $priceRanges = $productSearchResult->getPriceRanges();
        $this->assertInternalType('array', $priceRanges);
        $this->assertCount(7, $priceRanges);

        $this->assertEquals(0, $priceRanges[0]->getProductCount());
        $this->assertEquals(0, $priceRanges[0]->getFrom());
        $this->assertEquals(2000, $priceRanges[0]->getTo());
        $this->assertEquals(0, $priceRanges[0]->getMin());
        $this->assertEquals(0, $priceRanges[0]->getMax());
        $this->assertEquals(0, $priceRanges[0]->getMean());
        $this->assertEquals(0, $priceRanges[0]->getSum());

        $this->assertEquals(1, $priceRanges[3]->getProductCount());
        $this->assertEquals(7000, $priceRanges[3]->getFrom());
        $this->assertEquals(10000, $priceRanges[3]->getTo());
        $this->assertEquals(7499, $priceRanges[3]->getMin());
        $this->assertEquals(7499, $priceRanges[3]->getMax());
        $this->assertEquals(7499, $priceRanges[3]->getMean());
        $this->assertEquals(7499, $priceRanges[3]->getSum());

        $this->assertEquals(0, $priceRanges[5]->getProductCount());
        $this->assertEquals(20000, $priceRanges[5]->getFrom());
        $this->assertEquals(50000, $priceRanges[5]->getTo());
        $this->assertEquals(null, $priceRanges[5]->getMin());
        $this->assertEquals(null, $priceRanges[5]->getMax());
        $this->assertEquals(0, $priceRanges[5]->getMean());
        $this->assertEquals(0, $priceRanges[5]->getSum());

        $this->assertEquals(7499, $productSearchResult->getMinPrice());
        $this->assertEquals(7499, $productSearchResult->getMaxPrice());
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