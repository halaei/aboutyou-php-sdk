<?php
/**
 * @auther nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Unit\Model;


use AboutYou\SDK\Model\Facet;
use AboutYou\SDK\Model\FacetGroup;

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
 