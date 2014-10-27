<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Unit;

use AboutYou\SDK\Constants;
use AboutYou\SDK\Criteria\ProductFields;
use AboutYou\SDK\QueryBuilder;

class QueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var QueryBuilder */
    private $queryBuilder;

    public function setUp()
    {
        $this->queryBuilder = new QueryBuilder();
    }

    public function testMultiQuery()
    {
        $query = $this->queryBuilder
            ->fetchAutocomplete('bar')
            ->fetchSuggest('foo')
        ;

        $expected = '[{"autocompletion":{"searchword":"bar"}},{"suggest":{"searchword":"foo"}}]';

        $this->assertEquals($expected, $query->getQueryString());
    }

    public function testFetchCategoriesByIds()
    {
        $query = $this->queryBuilder
            ->fetchCategoriesByIds(array(789,456))
        ;
        $expected = '[{"category":{"ids":[789,456]}}]';
        $this->assertEquals($expected, $query->getQueryString());

        $this->queryBuilder = new QueryBuilder();
        $query = $this->queryBuilder
            ->fetchCategoriesByIds(array(4 => 789, 2 => 456))
        ;
        $expected = '[{"category":{"ids":[789,456]}}]';
        $this->assertEquals($expected, $query->getQueryString());
    }

    public function testFetchProductsByIds()
    {
        $query = $this->queryBuilder
            ->fetchProductsByIds(array(789,456))
        ;
        $expected = '[{"products":{"ids":[789,456],"fields":[]}}]';
        $this->assertEquals($expected, $query->getQueryString());

        $this->queryBuilder = new QueryBuilder();
        $query = $this->queryBuilder
            ->fetchProductsByIds(array(4 => 789, 2 => 456))
        ;
        $expected = '[{"products":{"ids":[789,456],"fields":[]}}]';
        $this->assertEquals($expected, $query->getQueryString());

        $this->queryBuilder = new QueryBuilder();
        $query = $this->queryBuilder
            ->fetchProductsByIds(array(4 => 789, 2 => 456), array(ProductFields::DESCRIPTION_SHORT))
        ;
        $expected = '[{"products":{"ids":[789,456],"fields":["description_short"]}}]';
        $this->assertEquals($expected, $query->getQueryString());

        // Test that attributes_merged were added, if facets are required
        $this->queryBuilder = new QueryBuilder();
        $query = $this->queryBuilder
            ->fetchProductsByIds(array(789, 456), array(ProductFields::BRAND))
        ;
        $expected = '[{"products":{"ids":[789,456],"fields":["brand_id","attributes_merged"]}}]';
        $this->assertEquals($expected, $query->getQueryString());
    }

    public function testFetchLiveVariantByIds()
    {
        $query = $this->queryBuilder
            ->fetchLiveVariantByIds(array(789,456))
        ;
        $expected = '[{"live_variant":{"ids":[789,456]}}]';
        $this->assertEquals($expected, $query->getQueryString());

        $this->queryBuilder = new QueryBuilder();
        $query = $this->queryBuilder
            ->fetchLiveVariantByIds(array(4 => 789, 2 => 456))
        ;
        $expected = '[{"live_variant":{"ids":[789,456]}}]';
        $this->assertEquals($expected, $query->getQueryString());
    }

    public function testFetchAutocomplete()
    {
        $queryFactory = function () {return new QueryBuilder();};

        $query = $queryFactory()->fetchAutocomplete('term');
        $expected = '[{"autocompletion":{"searchword":"term"}}]';
        $this->assertEquals($expected, $query->getQueryString());

        $query = $queryFactory()->fetchAutocomplete('Term');
        $expected = '[{"autocompletion":{"searchword":"term"}}]';
        $this->assertEquals($expected, $query->getQueryString());

        $query = $queryFactory()->fetchAutocomplete('Gürtel');
        $expected = '[{"autocompletion":{"searchword":"g\u00fcrtel"}}]';
        $this->assertEquals($expected, $query->getQueryString());

        $query = $queryFactory()->fetchAutocomplete('term', 10);
        $expected = '[{"autocompletion":{"searchword":"term","limit":10}}]';
        $this->assertEquals($expected, $query->getQueryString());

        $query = $queryFactory()->fetchAutocomplete('term', null, array(Constants::TYPE_CATEGORIES));
        $expected = '[{"autocompletion":{"searchword":"term","types":["categories"]}}]';
        $this->assertEquals($expected, $query->getQueryString());

        $query = $queryFactory()->fetchAutocomplete('term', 15, array(Constants::TYPE_CATEGORIES, Constants::TYPE_PRODUCTS));
        $expected = '[{"autocompletion":{"searchword":"term","limit":15,"types":["categories","products"]}}]';
        $this->assertEquals($expected, $query->getQueryString());

        $query = $queryFactory()->fetchAutocomplete('term', "12", array());
        $expected = '[{"autocompletion":{"searchword":"term","limit":12}}]';
        $this->assertEquals($expected, $query->getQueryString());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider fetchAutocompleteThrowsInvalidArgumentExceptionProvider
     */
    public function testFetchAutocompleteThrowsInvalidArgumentException()
    {
        call_user_func_array(array(new QueryBuilder(), 'fetchAutocomplete'), func_get_args());
    }

    public function fetchAutocompleteThrowsInvalidArgumentExceptionProvider()
    {
        return array(
            array(124),
            array('term', 'all'),
            array('term', 10.0),
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The basket id must be a string
     */
    public function testFetchBasketThrowsInvalidArgumentException()
    {
        $this->queryBuilder->fetchBasket(123456789);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The basket id must have at least 5 characters
     */
    public function testFetchBasketThrowsInvalidArgumentException2()
    {
        $this->queryBuilder->fetchBasket('1234');
    }
}
 