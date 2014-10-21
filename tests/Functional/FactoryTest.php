<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Functional;


use \AY;

class FactoryTest extends AbstractShopApiTest
{
    public function testGetFactory()
    {
        $shopApi = new AY('id', 'dummy');

        $factory = $shopApi->getResultFactory();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Factory\\ModelFactoryInterface', $factory);
        $this->assertInstanceOf('\\AboutYou\\SDK\\Factory\\ResultFactoryInterface', $factory);

        $variant = $factory->createVariant(json_decode('{}'), $this->getProduct());
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Variant', $variant);

        $json = $this->getJsonObjectFromFile('facet.json');
        $facet = $factory->createFacet($json);
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Facet', $facet);

        $json = $this->getJsonObjectFromFile('product/product-full.json');
        $product = $factory->createProduct($json);
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Product', $product);
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