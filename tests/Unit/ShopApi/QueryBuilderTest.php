<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Unit\ShopApi;

use Collins\ShopApi\Constants;
use Collins\ShopApi\QueryBuilder;

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
            ->fetchCategoryTree(0)
            ->fetchSuggest('foo')
        ;

        $expected = '[{"category_tree":{"max_depth":0}},{"suggest":{"searchword":"foo"}}]';

        $this->assertEquals($expected, $query->getQueryString());
    }

    public function testFetchCategoryTree()
    {
        $query = $this->queryBuilder
            ->fetchCategoryTree()
        ;

        $expected = '[{"category_tree":{}}]';

        $this->assertEquals($expected, $query->getQueryString());
    }

    public function testFetchAutocomplete()
    {
        $query = (new QueryBuilder())->fetchAutocomplete('term');
        $expected = '[{"autocompletion":{"searchword":"term"}}]';
        $this->assertEquals($expected, $query->getQueryString());

        $query = (new QueryBuilder())->fetchAutocomplete('Term');
        $expected = '[{"autocompletion":{"searchword":"term"}}]';
        $this->assertEquals($expected, $query->getQueryString());

        $query = (new QueryBuilder())->fetchAutocomplete('term', 10);
        $expected = '[{"autocompletion":{"searchword":"term","limit":10}}]';
        $this->assertEquals($expected, $query->getQueryString());

        $query = (new QueryBuilder())->fetchAutocomplete('term', null, array(Constants::TYPE_CATEGORIES));
        $expected = '[{"autocompletion":{"searchword":"term","types":["categories"]}}]';
        $this->assertEquals($expected, $query->getQueryString());

        $query = (new QueryBuilder())->fetchAutocomplete('term', 15, array(Constants::TYPE_CATEGORIES, Constants::TYPE_PRODUCTS));
        $expected = '[{"autocompletion":{"searchword":"term","limit":15,"types":["categories","products"]}}]';
        $this->assertEquals($expected, $query->getQueryString());

        $query = (new QueryBuilder())->fetchAutocomplete('term', "12", array());
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
     * @expectedExceptionMessage The session id must be a string
     */
    public function testFetchBasketThrowsInvalidArgumentException()
    {
        $this->queryBuilder->fetchBasket(123456789);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The session id must have at least 5 characters
     */
    public function testFetchBasketThrowsInvalidArgumentException2()
    {
        $this->queryBuilder->fetchBasket('1234');
    }
}
 