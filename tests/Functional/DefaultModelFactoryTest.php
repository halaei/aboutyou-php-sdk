<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Functional;

use \AY;

class DefaultModelFactoryTest extends AbstractAYTest
{
    /**
     * @group facet-manager
     */
    public function testFacetManager()
    {
        $ay = $this->getAYWithResultFile('facets-all.json');
        /** @var \AboutYou\SDK\Factory\DefaultModelFactory $modelFactory */
        $modelFactory = $ay->getResultFactory();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\FacetManager\\DefaultFacetManager', $modelFactory->getFacetManager());

        $facetManagerMock = $this->getMockForAbstractClass('\\AboutYou\\SDK\\Model\\FacetManager\\FacetManagerInterface');
        $facetManagerMock->expects($this->atLeastOnce())
            ->method('getFacet');

        $modelFactory->setFacetManager($facetManagerMock);
        $product = $modelFactory->createProduct(json_decode('{
            "id": 264558,
            "name": "Used Jeansjacke",
            "attributes_merged": {
                "attributes_0": [641],
                "attributes_1": [1]
            }
        }'));

        $brand = $product->getBrand();
    }
}
 