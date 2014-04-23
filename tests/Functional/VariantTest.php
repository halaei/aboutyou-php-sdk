<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
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
}
 