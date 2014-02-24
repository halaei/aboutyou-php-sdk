<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
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
}
 