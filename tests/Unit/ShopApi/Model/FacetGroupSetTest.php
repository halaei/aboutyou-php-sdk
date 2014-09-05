<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Unit\Model;

use Collins\ShopApi\Model\FacetGroupSet;
use Collins\ShopApi\Model\FacetManager\SingleFacetManager;
use Symfony\Component\EventDispatcher\Event;

class FacetGroupSetTest extends AbstractModelTest
{
    public function testGetUniqueKey()
    {
        $facetGroupSet1 = new FacetGroupSet(array(4 => array(3,2)));
        $facetGroupSet2 = new FacetGroupSet(array(4 => array(2,3)));

        $this->assertEquals(
            $facetGroupSet1->getUniqueKey(),
            $facetGroupSet2->getUniqueKey()
        );

        $facetGroupSet1 = new FacetGroupSet(array(1=>array(2),3=>array(4)));
        $facetGroupSet2 = new FacetGroupSet(array(3=>array(4),1=>array(2)));

        $this->assertEquals(
            $facetGroupSet1->getUniqueKey(),
            $facetGroupSet2->getUniqueKey()
        );
    }

    public function testGetLazyGroups()
    {
        $facetGroupSet = new FacetGroupSet(array(1=>array(2),3=>array(4)));
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
        return array(
            array(
                array(4 => array(3,2)),
                array(4 => array(2,3))
            ),
            array(
                array(4 => array(3,2), 5 => array(6)),
                array(4 => array(2,3))
            ),
            array(
                array(4 => array(3,2), 5 => array(6)),
                array(5 => array(6))
            ),
        );
    }

    /**
     * @dataProvider containsFalseProvider
     */
    public function testContainsFalse($a1, $a2)
    {
        $facetGroupSet1 = new FacetGroupSet(array(4 => array(3,2)));
        $facetGroupSet2 = new FacetGroupSet(array(4 => array(2)));

        $this->assertFalse($facetGroupSet1->contains($facetGroupSet2));
    }

    public function containsFalseProvider()
    {
        return array(
            array(
                array(4 => array(3,2)),
                array(4 => array(2))
            ),
            array(
                array(4 => array(3,2), 5 => array(6)),
                array(3 => array(2,3))
            ),
            array(
                array(4 => array(3,2), 5 => array(6)),
                array(5 => array(7))
            ),
        );
    }

    public function testSetFacetManager()
    {
        FacetGroupSet::setFacetManager(new SingleFacetManager());
    }

    public function testUsingFacetManager()
    {
        $facetManagerMock = $this->getMockForAbstractClass('Collins\\ShopApi\\Model\\FacetManager\\FacetManagerInterface');
        $facetManagerMock->expects($this->atLeastOnce())
            ->method('getFacet');

        FacetGroupSet::setFacetManager($facetManagerMock);
        $facetGroupSet = new FacetGroupSet(array(0=>array(123)));

        $facetGroup = $facetGroupSet->getGroups();
    }

    public function testHasGroup()
    {
        $facetGroupSet = new FacetGroupSet(array(4 => array(3,2)));
        $this->assertTrue($facetGroupSet->hasGroup(4));
        $this->assertFalse($facetGroupSet->hasGroup(3));
    }
}
 