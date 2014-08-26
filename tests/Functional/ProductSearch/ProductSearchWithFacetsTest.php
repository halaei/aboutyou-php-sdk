<?php

namespace Collins\ShopApi\Test\Functional\ProductSearch;

use Collins\ShopApi\Model\ProductSearchResult;
use Collins\ShopApi\Test\Functional\AbstractShopApiTest;

class ProductSearchWithFacetsTest extends AbstractShopApiTest
{
    public function testProductSearchWithSaleResult()
    {
        $shopApi = $this->getShopApiWithResultFiles(array(
                'result-product-search-with-facets.json',
                'category-all.json',
                'facet-result.json'
            ));

        $productSearchResult = $shopApi->fetchProductSearch($shopApi->getProductSearchCriteria('12345'));

        $saleFacet = $productSearchResult->getSaleCounts();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\ProductSearchResult\\SaleCounts', $saleFacet);
        $this->assertEquals(25303, $saleFacet->getProductCountTotal());
        $this->assertEquals(5261, $saleFacet->getProductCountInSale());
        $this->assertEquals(20042, $saleFacet->getProductCountNotInSale());
    }

    public function testProductSearchWithFacetResult()
    {
        $shopApi = $this->getShopApiWithResultFiles(array(
            'result-product-search-with-facets.json',
            'category-all.json',
            'facet-result.json'
        ));

        // get all available products
        $productSearchResult = $shopApi->fetchProductSearch($shopApi->getProductSearchCriteria('12345'));
        $facetsCounts = $productSearchResult->getFacets();
        $this->assertInternalType('array', $facetsCounts);
        $this->assertCount(1, $facetsCounts);
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\ProductSearchResult\\FacetCounts', $facetsCounts[0]);
        $this->assertEquals(25303, $facetsCounts[0]->getProductCountTotal());
        $this->assertEquals(20733, $facetsCounts[0]->getProductCountWithOtherFacetId());
        $this->assertEquals(0, $facetsCounts[0]->getProductCountWithoutAnyFacet());
        $facetCounts = $facetsCounts[0]->getFacetCounts();
        $this->assertCount(3, $facetCounts);

        foreach ($facetCounts as $facetCount) {
            $this->assertInstanceOf('Collins\\ShopApi\\Model\\ProductSearchResult\\FacetCount', $facetCount);
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
        $shopApi = $this->getShopApiWithResultFiles(array(
            'result-product-search-with-facets.json',
            'category-all.json',
            'facet-result.json'
        ));

        // get all available products
        $productSearchResult = $shopApi->fetchProductSearch($shopApi->getProductSearchCriteria('12345'));
        $categories = $productSearchResult->getCategories();
        $this->assertInternalType('array', $categories);

        $this->assertCount(361, $categories);

        foreach ($categories as $category) {
            $this->assertInstanceOf('Collins\\ShopApi\\Model\\Category', $category);
            $this->assertGreaterThan(0, $category->getProductCount());
        }

        $damenCategory = $categories['16077'];
        $this->assertNull($damenCategory->getParent());
        $subCategories = $damenCategory->getSubCategories();
        $this->assertCount(6, $subCategories);
        $this->assertEquals($damenCategory, $subCategories[0]->getParent());
//
//
//        $tree = $productSearchResult->getCategoryTree();
//        $this->assertInternalType('array', $tree);
//        $this->assertCount(3, $tree);
//
//        foreach ($tree as $category) {
//            $this->assertInstanceOf('Collins\\ShopApi\\Model\\Category', $category);
//            $this->assertNull(0, $category->getParent());
//            $this->assertNotCount(0, $category->getSubCategories());
//        }
    }

    public function testProductSearchGetActiveBrands()
    {
        $this->markTestIncomplete('implement method');

//        $shopApi = $this->getShopApiWithResultFiles(array(
//                'result-product-search-with-facets.json',
//                'category-all.json'
//            ));
//
//        $criteria = $shopApi->getProductSearchCriteria('12345')
//            ->setLimit(0)
//            ->selectFacetsByFacetGroup(0, -1);
//        $productSearchResult = $shopApi->fetchProductSearch($criteria);
//        $brandRawFacetIds = $productSearchResult->getRawFacets();
//
//        // collect ids
//        $brandFacetIds = array();
//
//        $brands = $shopApi->fetchFacets($brandFacetIds);

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