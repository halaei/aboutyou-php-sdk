<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;


use Collins\ShopApi;

class FactoryTestAbstract extends AbstractShopApiTest
{
    public function testGetFactory()
    {
        $shopApi = new ShopApi('id', 'dummy');

        $factory = $shopApi->getResultFactory();
        $this->assertInstanceOf('Collins\\ShopApi\\Factory\\ModelFactoryInterface', $factory);
        $this->assertInstanceOf('Collins\\ShopApi\\Factory\\ResultFactoryInterface', $factory);

        $variant = $factory->createVariant(json_decode('{}'));
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Variant', $variant);

        $json = $this->getJsonObjectFromFile('facet.json');
        $facet = $factory->createFacet($json);
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Facet', $facet);

        $json = $this->getJsonObjectFromFile('product/product-full.json');
        $product = $factory->createProduct($json);
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Product', $product);
    }

    public function testGetRawJsonFactory()
    {
//        $this->markTestIncomplete('fatal error');
        $shopApi = new ShopApi('id', 'dummy');
        $shopApi->setResultFactory(new ShopApi\Factory\RawJsonFactory($shopApi));

        $factory = $shopApi->getResultFactory();
        $this->assertInstanceOf('Collins\\ShopApi\\Factory\\ResultFactoryInterface', $factory);
        $this->assertNotInstanceOf('Collins\\ShopApi\\Factory\\ModelFactoryInterface', $factory);

        $tree = $factory->createCategoryTree(json_decode('{}'));
        $this->assertInternalType('array', $tree);

        $json = $this->getJsonObjectFromFile('fetch-facet.json');
        $facets = $factory->createFacetList($json);
        $this->assertInternalType('array', $facets);

        $json = $this->getJsonObjectFromFile('result/products-full.json');
        $result = $factory->createProductsResult($json[0]->products);
        $this->assertInstanceOf('\stdClass', $result);
    }
}