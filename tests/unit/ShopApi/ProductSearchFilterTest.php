<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Unit\ShopApi;

use Collins\ShopApi\Model\FacetGroupSet;
use Collins\ShopApi\ProductSearchFilter;

class ProductSearchFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testToArray()
    {
        $filter = new ProductSearchFilter();
        $this->assertEquals([], $filter->toArray());

        $filter = ProductSearchFilter::create()
            ->addIsSale(false);
        $this->assertEquals(['sale' => false], $filter->toArray());

        $filter = ProductSearchFilter::create()
            ->addCategories([123, 456]);
        $this->assertEquals(['categories' => [123, 456]], $filter->toArray());

        $filter = ProductSearchFilter::create()
            ->addPrice(123);
        $this->assertEquals(['prices' => ['from' => 123]], $filter->toArray());
        $filter = ProductSearchFilter::create()
            ->addPrice(0, 123);
        $this->assertEquals(['prices' => ['to' => 123]], $filter->toArray());
        $filter = ProductSearchFilter::create()
            ->addPrice(123, 456);
        $this->assertEquals(['prices' => ['from' => 123, 'to' => 456]], $filter->toArray());
        $filter = ProductSearchFilter::create()
            ->addPrice(-1);
        $this->assertEquals(['prices' => []], $filter->toArray());
        $filter = ProductSearchFilter::create()
            ->addPrice(123, -1);
        $this->assertEquals(['prices' => ['from' => 123]], $filter->toArray());

        $filter = ProductSearchFilter::create()
            ->addSearchword('word1 word2');
        $this->assertEquals(['searchword' => 'word1 word2'], $filter->toArray());

        $filter = ProductSearchFilter::create()
            ->addSearchword('word')
            ->addIsSale(null);
        $this->assertEquals(['searchword' => 'word', 'sale' => null], $filter->toArray());
    }

    public function testAddAttributes()
    {
        $filter = ProductSearchFilter::create()
            ->addAttributes([0 => [264]]);
        $this->assertEquals(['facets' => [0 => [264]]], $filter->toArray());

        $this->markTestIncomplete();
//        $filter = ProductSearchFilter::create()
//            ->addAttributes(new FacetGroupSet([0 => [264]]));
//        $this->assertEquals(['facets' => [0 => [264]]], $filter->toArray());
    }
}
 