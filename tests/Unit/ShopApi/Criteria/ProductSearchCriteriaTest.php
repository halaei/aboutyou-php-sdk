<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Unit\AboutYou\SDK\Criteria;


use AboutYou\SDK\Criteria\ProductFields;
use AboutYou\SDK\Criteria\ProductSearchCriteria;
use AboutYou\SDK\Model\FacetGroup;
use AboutYou\SDK\Model\Facet;
use AboutYou\SDK\Model\FacetGroupSet;
use AboutYou\SDK\Model\Product;

class ProductSearchCriteriaTest extends \AboutYou\SDK\Test\ShopSdkTest
{
    public function getCriteria()
    {
        return new ProductSearchCriteria('my');
    }

    public function testToArray()
    {
        $criteria = $this->getCriteria();

        $this->assertInstanceOf('\\AboutYou\\SDK\\Criteria\\CriteriaInterface', $criteria);
        $this->assertInstanceOf('\\AboutYou\\SDK\\Criteria\\ProductSearchCriteria', $criteria);
        $this->assertEquals('{"session_id":"my"}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->setLimit(11,12);
        $this->assertEquals('{"session_id":"my","result":{"limit":11,"offset":12}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->boostProducts(array(1,2,3));
        $this->assertEquals('{"session_id":"my","result":{"boosts":[1,2,3]}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->selectCategories();
        $this->assertEquals('{"session_id":"my","result":{"categories":true}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->selectPriceRanges();
        $this->assertEquals('{"session_id":"my","result":{"price":true}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->selectSales();
        $this->assertEquals('{"session_id":"my","result":{"sale":true}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->sortBy(ProductSearchCriteria::SORT_TYPE_PRICE, ProductSearchCriteria::SORT_DESC);
        $this->assertEquals('{"session_id":"my","result":{"sort":{"by":"price","direction":"desc"}}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->selectFacetsByGroupId(206, 3);
        $this->assertEquals('{"session_id":"my","result":{"facets":{"206":{"limit":3}}}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->selectAllFacets(2);
        $this->assertEquals('{"session_id":"my","result":{"facets":{"_all":{"limit":2}}}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->selectFacetsByFacetGroup(new FacetGroup(0, 'brand'), 4);
        $this->assertEquals('{"session_id":"my","result":{"facets":{"0":{"limit":4}}}}', json_encode($criteria->toArray()));
        $criteria = $this->getCriteria()
            ->selectFacetsByFacetGroup(new Facet(1234, '', '', 1, 'brand'), 3);
        $this->assertEquals('{"session_id":"my","result":{"facets":{"1":{"limit":3}}}}', json_encode($criteria->toArray()));
        $criteria = $this->getCriteria()
            ->selectFacetsByFacetGroup(new FacetGroup('0', 'brand'), 4)
            ->selectFacetsByGroupId(206, 5);
        $this->assertEquals('{"session_id":"my","result":{"facets":{"0":{"limit":4},"206":{"limit":5}}}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->selectProductFields(array(ProductFields::IS_ACTIVE));
        $this->assertEquals('{"session_id":"my","result":{"fields":["active"]}}', json_encode($criteria->toArray()));
        $criteria = $this->getCriteria()
            ->selectProductFields(array(ProductFields::BRAND, ProductFields::IS_ACTIVE));
        $this->assertEquals('{"session_id":"my","result":{"fields":["brand_id","active","attributes_merged"]}}', json_encode($criteria->toArray()));
        $criteria = $this->getCriteria()
            ->selectProductFields(array(ProductFields::VARIANTS));
        $this->assertEquals('{"session_id":"my","result":{"fields":["variants","attributes_merged"]}}', json_encode($criteria->toArray()));
        $criteria = $this->getCriteria()
            ->selectProductFields(array(ProductFields::DEFAULT_VARIANT));
        $this->assertEquals('{"session_id":"my","result":{"fields":["default_variant","attributes_merged"]}}', json_encode($criteria->toArray()));
        $criteria = $this->getCriteria()
            ->selectProductFields(array(ProductFields::BRAND, ProductFields::IS_ACTIVE, ProductFields::BRAND));
        $this->assertEquals('{"session_id":"my","result":{"fields":["brand_id","active","attributes_merged"]}}', json_encode($criteria->toArray()));
        $criteria = $this->getCriteria()
            ->selectProductFields(array(ProductFields::BRAND, ProductFields::ATTRIBUTES_MERGED, ProductFields::IS_ACTIVE));
        $this->assertEquals('{"session_id":"my","result":{"fields":["brand_id","attributes_merged","active"]}}', json_encode($criteria->toArray()));

        $criteria = new ProductSearchCriteria('12345');
        $this->assertEquals(array('session_id' => '12345'), $criteria->toArray());

        $criteria = ProductSearchCriteria::create('12345')
            ->filterBySale(false);
        $this->assertEquals(array('session_id' => '12345', 'filter' => array('sale' => false)), $criteria->toArray());
        $this->assertEquals(false, $criteria->getSaleFilter());

        $criteria = ProductSearchCriteria::create('12345')
            ->filterByCategoryIds(array(123, 456));
        $this->assertEquals(array('session_id' => '12345', 'filter' => array('categories' => array(123, 456))), $criteria->toArray());
        $this->assertEquals('{"session_id":"12345","filter":{"categories":[123,456]}}', json_encode($criteria->toArray()));
        $criteria->filterByCategoryIds(array(789, 456));
        $this->assertEquals(array('session_id' => '12345', 'filter' => array('categories' => array(789, 456))), $criteria->toArray());
        $this->assertEquals('{"session_id":"12345","filter":{"categories":[789,456]}}', json_encode($criteria->toArray()));
        $criteria->filterByCategoryIds(array(123,456), true);
        $this->assertEquals(array('session_id' => '12345', 'filter' => array('categories' => array(789, 456, 123))), $criteria->toArray());
        $this->assertEquals('{"session_id":"12345","filter":{"categories":[789,456,123]}}', json_encode($criteria->toArray()));
        $this->assertEquals(array(789, 456, 123), $criteria->getCategoryFilter());
        $criteria->filterByCategoryIds(array(123, 456, 123, 789));
        $this->assertEquals(array('session_id' => '12345', 'filter' => array('categories' => array(123, 456, 789))), $criteria->toArray());
        $this->assertEquals('{"session_id":"12345","filter":{"categories":[123,456,789]}}', json_encode($criteria->toArray()));

        $criteria = ProductSearchCriteria::create('12345')
            ->filterByPriceRange(123);
        $this->assertEquals(array('session_id' => '12345', 'filter' => array('prices' => array('from' => 123))), $criteria->toArray());
        $criteria = ProductSearchCriteria::create('12345')
            ->filterByPriceRange(0, 123);
        $this->assertEquals(array('session_id' => '12345', 'filter' => array('prices' => array('to' => 123))), $criteria->toArray());
        $criteria = ProductSearchCriteria::create('12345')
            ->filterByPriceRange(123, 456);
        $this->assertEquals(array('session_id' => '12345', 'filter' => array('prices' => array('from' => 123, 'to' => 456))), $criteria->toArray());
        $this->assertEquals('{"session_id":"12345","filter":{"prices":{"from":123,"to":456}}}', json_encode($criteria->toArray()));
        $this->assertEquals(array('to' => 456, 'from' => 123), $criteria->getPriceRangeFilter());

        $criteria = ProductSearchCriteria::create('12345')
            ->filterByPriceRange(-1);
        $this->assertEquals(array('session_id' => '12345', 'filter' => array('prices' => array())), $criteria->toArray());
        $criteria = ProductSearchCriteria::create('12345')
            ->filterByPriceRange(123, -1);
        $this->assertEquals(array('session_id' => '12345', 'filter' => array('prices' => array('from' => 123))), $criteria->toArray());

        $criteria = ProductSearchCriteria::create('12345')
            ->filterBySearchword('word1 word2');
        $this->assertEquals(array('session_id' => '12345', 'filter' => array('searchword' => 'word1 word2')), $criteria->toArray());
        $this->assertEquals('word1 word2', $criteria->getSearchwordFilter());

        $criteria = ProductSearchCriteria::create('12345')
            ->boostProducts(array(123,"456",123,789));
        $this->assertEquals('{"session_id":"12345","result":{"boosts":[123,456,789]}}', json_encode($criteria->toArray()));

        $criteria = ProductSearchCriteria::create('12345')
            ->filterBySearchword('word')
            ->filterBySale(null);
        $this->assertEquals(array('session_id' => '12345', 'filter' => array('searchword' => 'word', 'sale' => null)), $criteria->toArray());
    }

    public function testAddAttributes()
    {
        $criteria = ProductSearchCriteria::create('12345')
            ->filterByFacetIds(array(0 => array(264)));
        $this->assertEquals('{"session_id":"12345","filter":{"facets":{"0":[264]}}}', json_encode($criteria->toArray()));
        $criteria->filterByFacetIds(array(2 => array(123)));
        $this->assertEquals('{"session_id":"12345","filter":{"facets":{"2":[123]}}}', json_encode($criteria->toArray()));
        $criteria->filterByFacetIds(array(2 => array(456)), true);
        $this->assertEquals('{"session_id":"12345","filter":{"facets":{"2":[123,456]}}}', json_encode($criteria->toArray()));
        $criteria->filterByFacetIds(array(1 => array(123)), true);
        $this->assertEquals('{"session_id":"12345","filter":{"facets":{"2":[123,456],"1":[123]}}}', json_encode($criteria->toArray()));
        $criteria->filterByFacetIds(array(2 => array(789,123)), true);
        $criteria->filterByFacetIds(array(2 => array(789)), true);
        $this->assertEquals('{"session_id":"12345","filter":{"facets":{"2":[123,456,789],"1":[123]}}}', json_encode($criteria->toArray()));

        $criteria = ProductSearchCriteria::create('12345')
            ->filterByFacetGroupSet(new FacetGroupSet(array(0 => array(264))));
        $this->assertEquals('{"session_id":"12345","filter":{"facets":{"0":[264]}}}', json_encode($criteria->toArray()));
        $criteria->filterByFacetGroupSet(new FacetGroupSet(array(0 => array(123))), true);
        $this->assertEquals('{"session_id":"12345","filter":{"facets":{"0":[264,123]}}}', json_encode($criteria->toArray()));

        $facetGroup = new FacetGroup(0, 'brand');
        $facetGroup->addFacet(new Facet(264, 'TOM', null, 0, 'brand'));
        $criteria = ProductSearchCriteria::create('12345')
            ->filterByFacetGroup($facetGroup);
        $this->assertEquals('{"session_id":"12345","filter":{"facets":{"0":[264]}}}', json_encode($criteria->toArray()));
        $facetGroup2 = new FacetGroup(0, 'brand');
        $facetGroup2->addFacet(new Facet(123, 'FOO', null, 0, 'brand'));
        $criteria->filterByFacetGroup($facetGroup2, true);
        $this->assertEquals('{"session_id":"12345","filter":{"facets":{"0":[264,123]}}}', json_encode($criteria->toArray()));

        $criteria->filterByFacetIds(array(2 => array(456)), true);
        $this->assertEquals('{"session_id":"12345","filter":{"facets":{"0":[264,123],"2":[456]}}}', json_encode($criteria->toArray()));
    }
}
