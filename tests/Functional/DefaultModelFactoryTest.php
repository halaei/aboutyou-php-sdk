<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class DefaultModelFactoryTest extends AbstractShopApiTest
{
    public function testFacetManager()
    {
        $shopApi = $this->getShopApiWithResultFile('facets-all.json');
        /** @var ShopApi\Factory\DefaultModelFactory $modelFactory */
        $modelFactory = $shopApi->getResultFactory();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\FacetManager', $modelFactory->getFacetManager());

        $facetManagerMock = $this->getMockForAbstractClass('Collins\\ShopApi\\Model\\FacetManagerInterface');
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

//        $this->markTestIncomplete('implement me');
    }
}
 