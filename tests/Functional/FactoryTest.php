<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;


use Collins\ShopApi;

class FactoryTestAbstract extends AbstractShopApiTest
{
    public function testGetFactory()
    {
//        $this->markTestIncomplete();

        $shopApi = new ShopApi('id', 'dummy');

        $factory = $shopApi->getModelFactory();
        $this->assertInstanceOf('Collins\\ShopApi\\Factory\\ModelFactoryInterface', $factory);

        $variant = $factory->createVariant(json_decode('{}'));
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Variant', $variant);

        $json = $this->getJsonObjectFromFile('facet.json');
        $facet = $factory->createFacet($json);
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Facet', $facet);

        $json = $this->getJsonObjectFromFile('product/product-full.json');
        $product = $factory->createProduct($json);
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Product', $product);
    }
} 