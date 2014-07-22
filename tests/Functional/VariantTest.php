<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi\Model\Product;
use Collins\ShopApi\Model\Variant;

class VariantTest extends AbstractShopApiTest
{
    public function testGetSize()
    {
        $shopApi = $this->getShopApiWithResultFileAndFacets(
            'result/products-374469-with-variants.json',
            'result/facet-for-product-374469.json'
        );
        $products = $shopApi->fetchProductsByIds(array(1234))->getProducts();
        /** @var Product $product */
        $product = reset($products);
        
        $variants = $product->getVariants();
        /** @var Variant $variant */
        $variant  = $variants[5894432];
        $facetGroup = $variant->getSize();
        $this->assertEquals('21', $facetGroup->getFacetNames());
        $this->assertEquals('size', $facetGroup->getName());

        $variant  = $variants[5894433];
        $facetGroup = $variant->getSize();
        $this->assertNull($facetGroup);

        $variant  = $variants[5894434];
        $facetGroup = $variant->getSize();
        $this->assertEquals('M', $facetGroup->getFacetNames());
        $this->assertEquals('clothing_unisex_int', $facetGroup->getName());

        $variant  = $variants[5894435];
        $facetGroup = $variant->getSize();
        $this->assertEquals('L', $facetGroup->getFacetNames());
        $this->assertEquals('clothing_unisex_int', $facetGroup->getName());

        $variant  = $variants[5894436];
        $facetGroup = $variant->getSize();
        $this->assertEquals('XL', $facetGroup->getFacetNames());
        $this->assertEquals('clothing_unisex_int', $facetGroup->getName());
    }
    
    public function testFetchVariantById()
    {
        $shopApi = $this->getShopApiWithResultFiles(array(
            'result/live-variant-for-5236546.json',
            'result/live-variant-product-294475.json',
            ));
        
        
        $result = $shopApi->fetchVariantsByIds(array('5236546'));
        
        $this->assertFalse($result->hasVariantsNotFound());
        
        $variant = $result->getVariantById(5236546);
        
        $this->assertInstanceOf('Collins\ShopApi\Model\Variant', $variant);
        $this->assertEquals(5236546, $variant->getId());
        
        $product = $variant->getProduct();
        $this->assertInstanceOf('Collins\ShopApi\Model\Product', $product);
        
        $this->assertEquals(294475, $product->getId());
    }
    
    public function testFetchVariantByIdWithMultiAndEqualProducts()
    {
        $shopApi = $this->getShopApiWithResultFiles(array(
            'result/live-variant-for-multi-and-equal-product.json',
            'result/live-variant-multi-and-equal-products.json',
            ));
        
        $ids = array(6077282, 6077305, 6501489);
        
        $result = $shopApi->fetchVariantsByIds($ids);
        
        $this->assertFalse($result->hasVariantsNotFound());
        
        $this->assertTrue($result->hasVariantsFound());
        
        $this->assertCount(3, $result->getVariantsFound());
        
        foreach ($ids as $id) {
            $variant = $result->getVariantById($id);
            $this->assertInstanceOf('Collins\ShopApi\Model\Variant', $variant);
            $this->assertInstanceOf('Collins\ShopApi\Model\Product', $variant->getProduct());
        }
    }    
    
    public function testFetchVariantByIdWithNoResult()
    {
        $shopApi = $this->getShopApiWithResultFile('result/live-variant-not-found.json');
        $ids =  array(123,456,166366);
        
        $result = $shopApi->fetchVariantsByIds($ids);
        
        $this->assertFalse($result->hasVariantsFound());
        $this->assertTrue($result->hasVariantsNotFound());
        
        $errors = $result->getVariantsNotFound();
        
        foreach ($ids as $id) {
            $this->assertTrue(in_array($id, $errors));
        }
    }     
    
    public function testFetchVariantByIdWithWrongProductSearchResult()
    {
        $shopApi = $this->getShopApiWithResultFiles(array(
            'result/live-variant-for-5236546.json',
            'result/live-variant-product-not-found.json',
            ));
          
        $result = $shopApi->fetchVariantsByIds(array('5236546'));
    
        $this->assertTrue($result->hasVariantsNotFound());
        $errors = $result->getVariantsNotFound();
        
        $this->assertEquals(5236546, $errors[0]);
    }    
    
    
    public function testGetProductFromVariant()
    {
        $shopApi = $this->getShopApiWithResultFileAndFacets(
            'result/products-374469-with-variants.json',
            'result/facet-for-product-374469.json'
        );
        
        $products = $shopApi->fetchProductsByIds(array(1234))->getProducts();
        /** @var Product $product */
        $product = reset($products);
        
        $variants = $product->getVariants();
        /** @var Variant $variant */
        
        $this->assertCount(5, $variants);
        
        foreach ($variants as $variant) {
            $this->assertInstanceOf('Collins\ShopApi\Model\Product', $variant->getProduct());
            $this->assertEquals(374469, $variant->getProduct()->getId());
        }
    }
}
 