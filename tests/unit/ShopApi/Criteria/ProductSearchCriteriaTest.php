<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Unit\ShopApi\Criteria;


use Collins\ShopApi\Criteria\ProductFields;
use Collins\ShopApi\Criteria\ProductSearchCriteria;
use Collins\ShopApi\Model\FacetGroup;
use Collins\ShopApi\Model\Facet;
use Collins\ShopApi\Model\FacetGroupSet;

class ProductSearchCriteriaTest extends \PHPUnit_Framework_TestCase
{
    public function getCriteria()
    {
        return new ProductSearchCriteria('my');
    }

    public function testToArray()
    {
        $criteria = $this->getCriteria();

        $this->assertInstanceOf('Collins\\ShopApi\\Criteria\\CriteriaInterface', $criteria);
        $this->assertInstanceOf('Collins\\ShopApi\\Criteria\\ProductSearchCriteria', $criteria);
        $this->assertEquals('{"session_id":"my"}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->setLimit(11,12);
        $this->assertEquals('{"session_id":"my","result":{"limit":11,"offset":12}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->setBoostProducts([1,2,3]);
        $this->assertEquals('{"session_id":"my","result":{"boost":[1,2,3]}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->selectCategoryFacets(true);
        $this->assertEquals('{"session_id":"my","result":{"categories":true}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->selectPrice(true);
        $this->assertEquals('{"session_id":"my","result":{"price":true}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->setSale(true);
        $this->assertEquals('{"session_id":"my","result":{"sale":true}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->sortBy(ProductSearchCriteria::SORT_TYPE_PRICE, ProductSearchCriteria::SORT_DESC);
        $this->assertEquals('{"session_id":"my","result":{"sort":{"by":"price","direction":"desc"}}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->setFacets(206, 3);
        $this->assertEquals('{"session_id":"my","result":{"facets":{"206":{"limit":3}}}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->setFacets(ProductSearchCriteria::FACETS_ALL, 2);
        $this->assertEquals('{"session_id":"my","result":{"facets":{"_all":{"limit":2}}}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->setFacets(new FacetGroup('0', 'brand'), 4);
        $this->assertEquals('{"session_id":"my","result":{"facets":{"0":{"limit":4}}}}', json_encode($criteria->toArray()));
        $criteria = $this->getCriteria()
            ->setFacets(new FacetGroup('0', 'brand'), 4)
            ->setFacets(206, 5);
        $this->assertEquals('{"session_id":"my","result":{"facets":{"0":{"limit":4},"206":{"limit":5}}}}', json_encode($criteria->toArray()));

        $criteria = $this->getCriteria()
            ->selectFields([ProductFields::BRAND, ProductFields::IS_ACTIVE]);
        $this->assertEquals('{"session_id":"my","result":{"fields":["brand_id","active"]}}', json_encode($criteria->toArray()));

        $criteria = new ProductSearchCriteria(12345);
        $this->assertEquals(['session_id' => 12345], $criteria->toArray());

        $criteria = ProductSearchCriteria::create(12345)
            ->setIsSale(false);

        $this->assertEquals(['session_id' => 12345, 'filter' => ['sale' => false]], $criteria->toArray());

        $criteria = ProductSearchCriteria::create(12345)
            ->addCategories([123, 456]);
        $this->assertEquals(['session_id' => 12345, 'filter' => ['categories' => [123, 456]]], $criteria->toArray());
        $this->assertEquals('{"session_id":12345,"filter":{"categories":[123,456]}}', json_encode($criteria->toArray()));

        $criteria = ProductSearchCriteria::create(12345)
            ->setPriceRange(123);
        $this->assertEquals(['session_id' => 12345, 'filter' => ['prices' => ['from' => 123]]], $criteria->toArray());
        $criteria = ProductSearchCriteria::create(12345)
            ->setPriceRange(0, 123);
        $this->assertEquals(['session_id' => '12345', 'filter' => ['prices' => ['to' => 123]]], $criteria->toArray());
        $criteria = ProductSearchCriteria::create(12345)
            ->setPriceRange(123, 456);
        $this->assertEquals(['session_id' => 12345, 'filter' => ['prices' => ['from' => 123, 'to' => 456]]], $criteria->toArray());
        $this->assertEquals('{"session_id":12345,"filter":{"prices":{"from":123,"to":456}}}', json_encode($criteria->toArray()));

        $criteria = ProductSearchCriteria::create(12345)
            ->setPriceRange(-1);
        $this->assertEquals(['session_id' => 12345, 'filter' => ['prices' => []]], $criteria->toArray());
        $criteria = ProductSearchCriteria::create(12345)
            ->setPriceRange(123, -1);
        $this->assertEquals(['session_id' => 12345, 'filter' => ['prices' => ['from' => 123]]], $criteria->toArray());

        $criteria = ProductSearchCriteria::create(12345)
            ->setSearchword('word1 word2');
        $this->assertEquals(['session_id' => 12345, 'filter' => ['searchword' => 'word1 word2']], $criteria->toArray());

        $criteria = ProductSearchCriteria::create(12345)
            ->setSearchword('word')
            ->setIsSale(null);
        $this->assertEquals(['session_id' => 12345, 'filter' => ['searchword' => 'word', 'sale' => null]], $criteria->toArray());
    }

    public function testAddAttributes()
    {
        $criteria = ProductSearchCriteria::create(12345)
            ->setAttributes([0 => [264]]);
        $this->assertEquals('{"session_id":12345,"filter":{"facets":{"0":[264]}}}', json_encode($criteria->toArray()));

        $criteria = ProductSearchCriteria::create(12345)
            ->setFacetGroupSet(new FacetGroupSet([0 => [264]]));
        $this->assertEquals('{"session_id":12345,"filter":{"facets":{"0":[264]}}}', json_encode($criteria->toArray()));

        $facetGroup = new FacetGroup(0, 'brand');
        $facetGroup->addFacet(new Facet(264, 'TOM', null, 0, 'brand'));
        $criteria = ProductSearchCriteria::create(12345)
            ->setFacetGroup($facetGroup);
        $this->assertEquals('{"session_id":12345,"filter":{"facets":{"0":[264]}}}', json_encode($criteria->toArray()));
    }
}
