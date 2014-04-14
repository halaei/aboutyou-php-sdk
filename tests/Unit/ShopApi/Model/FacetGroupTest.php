<?php
/**
 * @auther nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Unit\Model;


use Collins\ShopApi\Model\Facet;
use Collins\ShopApi\Model\FacetGroup;

class FacetGroupTest extends AbstractModelTest
{
    public function testGetFacetNames()
    {
        $facetGroup = new FacetGroup(123, 'FacetGroup');
        $this->assertEquals('', $facetGroup->getFacetNames());

        $facetGroup->addFacet(new Facet(456, 'Foo', 'foo', 123, 'FacetGroup'));
        $this->assertEquals('Foo', $facetGroup->getFacetNames());
        $this->assertEquals('Foo', $facetGroup->getFacetNames(','));

        $facetGroup->addFacet(new Facet(789, 'Bar', 'bar', 123, 'FacetGroup'));
        $this->assertEquals('Foo/Bar', $facetGroup->getFacetNames());
        $this->assertEquals('Foo,Bar', $facetGroup->getFacetNames(','));
    }
}
 