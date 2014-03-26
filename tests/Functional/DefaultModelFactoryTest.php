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
        $shopApi = new ShopApi('id', 'token');
        /** @var ShopApi\Factory\DefaultModelFactory $modelFactory */
        $modelFactory = $shopApi->getResultFactory();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\FacetManager', $modelFactory->getFacetManager());

        $facetManagerMock = $this->getMockForAbstractClass('Collins\\ShopApi\\Model\\FacetManagerInterface');
        $facetManagerMock->expects($this->atLeastOnce())
            ->method('getFacet');

        $modelFactory->setFacetManager($facetManagerMock);
        
    }
}
 