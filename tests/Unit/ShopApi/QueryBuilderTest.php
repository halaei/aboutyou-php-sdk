<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Unit\ShopApi;

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

    /**
     * @expectedException \Collins\ShopApi\Exception\InvalidParameterException
     * @expectedExceptionMessage The session id must be a string
     */
    public function testFetchBasketThrowsInvalidParameterException()
    {
        $this->queryBuilder->fetchBasket(123456789);
    }

    /**
     * @expectedException \Collins\ShopApi\Exception\InvalidParameterException
     * @expectedExceptionMessage The session id must have at least 5 characters
     */
    public function testFetchBasketThrowsInvalidParameterException2()
    {
        $this->queryBuilder->fetchBasket('1234');
    }
}
 