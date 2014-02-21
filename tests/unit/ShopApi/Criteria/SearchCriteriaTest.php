<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Unit\ShopApi\Criteria;


use Collins\ShopApi\Criteria\ProductFields;
use Collins\ShopApi\Criteria\SearchCriteria;
use Collins\ShopApi\Model\FacetGroup;

class SearchCriteriaTest extends \PHPUnit_Framework_TestCase
{
    public function getCriteria()
    {
        return new SearchCriteria('my');
    }

    public function testToArray()
    {
        $criteria = $this->getCriteria();

        $this->assertInstanceOf('Collins\\ShopApi\\Criteria\\SearchCriteriaInterface', $criteria);
        $this->assertInstanceOf('Collins\\ShopApi\\Criteria\\SearchCriteria', $criteria);
        $this->assertEquals('{"session_id":"my"}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->setLimit(11,12);
        $this->assertEquals('{"session_id":"my","result":{"limit":11,"offset":12}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->boostProducts([1,2,3]);
        $this->assertEquals('{"session_id":"my","result":{"boost":[1,2,3]}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->categoryFacets(true);
        $this->assertEquals('{"session_id":"my","result":{"categories":true}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->priceFactes(true);
        $this->assertEquals('{"session_id":"my","result":{"price":true}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->saleFacets(true);
        $this->assertEquals('{"session_id":"my","result":{"sale":true}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->sortBy(SearchCriteria::SORT_TYPE_PRICE, SearchCriteria::SORT_DESC);
        $this->assertEquals('{"session_id":"my","result":{"sort":{"by":"price","direction":"desc"}}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->otherFacets(206, 3);
        $this->assertEquals('{"session_id":"my","result":{"facets":{"206":{"limit":3}}}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->otherFacets(SearchCriteria::FACETS_ALL, 2);
        $this->assertEquals('{"session_id":"my","result":{"facets":{"_all":{"limit":2}}}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->otherFacets(new FacetGroup('0', 'brand'), 4);
        $this->assertEquals('{"session_id":"my","result":{"facets":{"0":{"limit":4}}}}', json_encode($criteria->toArray()));
        $criteria = $this->getCriteria()
            ->otherFacets(new FacetGroup('0', 'brand'), 4)
            ->otherFacets(206, 5);
        $this->assertEquals('{"session_id":"my","result":{"facets":{"0":{"limit":4},"206":{"limit":5}}}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->selectFields([ProductFields::BRAND, ProductFields::IS_ACTIVE]);
        $this->assertEquals('{"session_id":"my","result":{"fields":["brand_id","active"]}}', json_encode($criteria->toArray()));
    }
}
 