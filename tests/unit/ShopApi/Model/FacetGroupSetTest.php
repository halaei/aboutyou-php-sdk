<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Unit\ShopApi\Model;


use Collins\ShopApi\Model\FacetGroupSet;

class FacetGroupSetTest extends AbstractModelTest
{
    public function testGetUniqueKey()
    {
        $facetGroupSet1 = new FacetGroupSet([4 => [3,2]]);
        $facetGroupSet2 = new FacetGroupSet([4 => [2,3]]);

        $this->assertEquals(
            $facetGroupSet1->getUniqueKey(),
            $facetGroupSet2->getUniqueKey()
        );

        $facetGroupSet1 = new FacetGroupSet([1=>[2],3=>[4]]);
        $facetGroupSet2 = new FacetGroupSet([3=>[4],1=>[2]]);

        $this->assertEquals(
            $facetGroupSet1->getUniqueKey(),
            $facetGroupSet2->getUniqueKey()
        );
    }

    public function testGetLazyGroups()
    {
        $facetGroupSet = new FacetGroupSet([1=>[2],3=>[4]]);
        $groups = $facetGroupSet->getLazyGroups();

        $this->assertCount(2, $groups);
        $this->assertEquals('1:2', $groups[1]->getUniqueKey());
        $this->assertEquals('3:4', $groups[3]->getUniqueKey());
    }

    /**
     * @dataProvider containsTrueProvider
     */
    public function testContainsTrue($a1, $a2)
    {
        $facetGroupSet1 = new FacetGroupSet($a1);
        $facetGroupSet2 = new FacetGroupSet($a2);

        $this->assertTrue($facetGroupSet1->contains($facetGroupSet2));
    }

    public function containsTrueProvider()
    {
        return [
            [
                [4 => [3,2]],
                [4 => [2,3]]
            ],
            [
                [4 => [3,2], 5 => [6]],
                [4 => [2,3]]
            ],
            [
                [4 => [3,2], 5 => [6]],
                [5 => [6]]
            ],
        ];
    }

    /**
     * @dataProvider containsFalseProvider
     */
    public function testContainsFalse($a1, $a2)
    {
        $facetGroupSet1 = new FacetGroupSet([4 => [3,2]]);
        $facetGroupSet2 = new FacetGroupSet([4 => [2]]);

        $this->assertFalse($facetGroupSet1->contains($facetGroupSet2));
    }

    public function containsFalseProvider()
    {
        return [
            [
                [4 => [3,2]],
                [4 => [2]]
            ],
            [
                [4 => [3,2], 5 => [6]],
                [3 => [2,3]]
            ],
            [
                [4 => [3,2], 5 => [6]],
                [5 => [7]]
            ],
        ];
    }
}
 