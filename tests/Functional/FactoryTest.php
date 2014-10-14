<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;


use Collins\ShopApi;

class FactoryTest extends AbstractShopApiTest
{
    public function testGetFactory()
    {
        $shopApi = new ShopApi('id', 'dummy');

        $factory = $shopApi->getResultFactory();
        $this->assertInstanceOf('Collins\\ShopApi\\Factory\\ModelFactoryInterface', $factory);
        $this->assertInstanceOf('Collins\\ShopApi\\Factory\\ResultFactoryInterface', $factory);

        $variant = $factory->createVariant(json_decode('{}'), $this->getProduct());
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Variant', $variant);

        $json = $this->getJsonObjectFromFile('facet.json');
        $facet = $factory->createFacet($json);
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Facet', $facet);

        $json = $this->getJsonObjectFromFile('product/product-full.json');
        $product = $factory->createProduct($json);
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Product', $product);
    }

    private function getProduct() 
    {
        $productIds = array(123, 456);

        $shopApi = $this->getShopApiWithResultFile('result/products.json');

        $productResult = $shopApi->fetchProductsByIds($productIds);
        $products = $productResult->getProducts();

        
        return $products[123];        
    }    
}