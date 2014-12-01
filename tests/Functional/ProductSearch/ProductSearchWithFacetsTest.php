<?php

namespace AboutYou\SDK\Test\Functional\ProductSearch;

use AboutYou\SDK\Model\ProductSearchResult;
use AboutYou\SDK\Test\Functional\AbstractAYTest;

class ProductSearchWithFacetsTest extends AbstractAYTest
{
    protected $facetsResultPath = null;

    public function testProductSearchWithSaleResult()
    {
        $ay = $this->getAYWithResultFile(
            'result-product-search-with-facets.json'
        );

        $productSearchResult = $ay->fetchProductSearch($ay->getProductSearchCriteria('12345'));

        $saleFacet = $productSearchResult->getSaleCounts();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\ProductSearchResult\\SaleCounts', $saleFacet);
        $this->assertEquals(25303, $saleFacet->getProductCountTotal());
        $this->assertEquals(5261, $saleFacet->getProductCountInSale());
        $this->assertEquals(20042, $saleFacet->getProductCountNotInSale());
    }

    public function testProductSearchWithSaleResultStrings()
    {
        $ay = $this->getAYWithResultFile(
            'result-product-search-with-facets-sale-terms-as-strings.json'
        );

        $productSearchResult = $ay->fetchProductSearch($ay->getProductSearchCriteria('12345'));

        $saleFacet = $productSearchResult->getSaleCounts();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\ProductSearchResult\\SaleCounts', $saleFacet);
        $this->assertEquals(25303, $saleFacet->getProductCountTotal());
        $this->assertEquals(5261, $saleFacet->getProductCountInSale());
        $this->assertEquals(20042, $saleFacet->getProductCountNotInSale());
    }

    public function testProductSearchWithPriceRangeResult()
    {
        $ay = $this->getAYWithResultFile(
            'result-product-search-with-facets.json'
        );

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

    public function testProductSearchWithFacetResult()
    {
        $ay = $this->getAYWithResultFileAndFacets(
            'result-product-search-with-facets.json',
            'facet-result.json'
        );

        // get all available products
        $productSearchResult = $ay->fetchProductSearch($ay->getProductSearchCriteria('12345'));
        $facetsCounts = $productSearchResult->getFacets();
        $this->assertInternalType('array', $facetsCounts);
        $this->assertCount(1, $facetsCounts);
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\ProductSearchResult\\FacetCounts', $facetsCounts[0]);
        $this->assertEquals(25303, $facetsCounts[0]->getProductCountTotal());
        $this->assertEquals(20733, $facetsCounts[0]->getProductCountWithOtherFacetId());
        $this->assertEquals(0, $facetsCounts[0]->getProductCountWithoutAnyFacet());
        $facetCounts = $facetsCounts[0]->getFacetCounts();
        $this->assertCount(3, $facetCounts);

        foreach ($facetCounts as $facetCount) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\ProductSearchResult\\FacetCount', $facetCount);
        }

        $this->assertEquals(1122, $facetCounts[0]->getId());
        $this->assertEquals('JACK & JONES', $facetCounts[0]->getName());
        $this->assertEquals(0, $facetCounts[0]->getGroupId());
        $this->assertEquals('brand', $facetCounts[0]->getGroupName());
        $this->assertEquals(2535, $facetCounts[0]->getProductCount());
        $this->assertEquals(121, $facetCounts[1]->getId());
        $this->assertEquals(1165, $facetCounts[1]->getProductCount());
        $this->assertEquals(266, $facetCounts[2]->getId());
        $this->assertEquals(870, $facetCounts[2]->getProductCount());

    }

    public function testProductSearchWithCategoriesResult()
    {
        $ay = $this->getAYWithResultFile(
            'result-product-search-with-facets.json'
        );

        // get all available products
        $productSearchResult = $ay->fetchProductSearch($ay->getProductSearchCriteria('12345'));
        $categories = $productSearchResult->getCategories();
        $this->assertInternalType('array', $categories);

        $this->assertCount(8, $categories);

        foreach ($categories as $category) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Category', $category);
            $this->assertGreaterThan(0, $category->getProductCount());
        }

        $damenCategory = reset($categories);
        $this->assertNull($damenCategory->getParent());
        $subCategories = $damenCategory->getSubCategories();
        $this->assertCount(3, $subCategories);
        $this->assertEquals($damenCategory, reset($subCategories)->getParent());


        $tree = $productSearchResult->getCategoryTree();
        $this->assertInternalType('array', $tree);
        $this->assertCount(2, $tree);

        foreach ($tree as $category) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Category', $category);
            $this->assertNull($category->getParent());
            $this->assertNotCount(0, $category->getSubCategories());
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