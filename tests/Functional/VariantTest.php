<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Functional;

use AboutYou\SDK\Model\Product;
use AboutYou\SDK\Model\Variant;

class VariantTest extends AbstractAYTest
{
    public function testGetSize()
    {
        $ay = $this->getAYWithResultFileAndFacets(
            'result/products-374469-with-variants.json',
            'result/facet-for-product-374469.json'
        );
        $products = $ay->fetchProductsByIds(array(1234))->getProducts();
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
        $this->markTestIncomplete();

        $ay = $this->getAYWithResultFiles(array(
            'result/live-variant-for-5236546.json',
            'result/live-variant-product-294475.json',
            ));


        $result = $ay->fetchVariantsByIds(array('5236546'));

        $this->assertFalse($result->hasVariantsNotFound());

        $variant = $result->getVariantById(5236546);

        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Variant', $variant);
        $this->assertEquals(5236546, $variant->getId());

        $product = $variant->getProduct();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Product', $product);

        $this->assertEquals(294475, $product->getId());
    }

    public function testFetchVariantByIdWithMultiAndEqualProducts()
    {
        $this->markTestIncomplete();

        $ay = $this->getAYWithResultFiles(array(
            'result/live-variant-for-multi-and-equal-product.json',
            'result/live-variant-multi-and-equal-products.json',
            ));

        $ids = array(6077282, 6077305, 6501489);

        $result = $ay->fetchVariantsByIds($ids);

        $this->assertFalse($result->hasVariantsNotFound());

        $this->assertTrue($result->hasVariantsFound());

        $this->assertCount(3, $result->getVariantsFound());

        foreach ($ids as $id) {
            $variant = $result->getVariantById($id);
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Variant', $variant);
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Product', $variant->getProduct());
        }
    }

    public function testFetchVariantByIdWithNoResult()
    {
        $this->markTestIncomplete();

        $ay = $this->getAYWithResultFile('result/live-variant-not-found.json');
        $ids =  array(123,456,166366);

        $result = $ay->fetchVariantsByIds($ids);

        $this->assertFalse($result->hasVariantsFound());
        $this->assertTrue($result->hasVariantsNotFound());

        $errors = $result->getVariantsNotFound();

        foreach ($ids as $id) {
            $this->assertTrue(in_array($id, $errors));
        }
    }

    public function testFetchVariantByIdWithWrongProductSearchResult()
    {
        $this->markTestIncomplete();

        $ay = $this->getAYWithResultFiles(array(
            'result/live-variant-for-5236546.json',
            'result/live-variant-product-not-found.json',
            ));

        $result = $ay->fetchVariantsByIds(array('5236546'));

        $this->assertTrue($result->hasVariantsNotFound());
        $errors = $result->getVariantsNotFound();

        $this->assertEquals(5236546, $errors[0]);
    }


    public function testGetProductFromVariant()
    {
        $this->markTestIncomplete();

        $ay = $this->getAYWithResultFileAndFacets(
            'result/products-374469-with-variants.json',
            'result/facet-for-product-374469.json'
        );

        $products = $ay->fetchProductsByIds(array(1234))->getProducts();
        /** @var Product $product */
        $product = reset($products);

        $variants = $product->getVariants();
        /** @var Variant $variant */

        $this->assertCount(5, $variants);

        foreach ($variants as $variant) {
            $this->assertInstanceOf('AboutYou\SDK\Model\Product', $variant->getProduct());
            $this->assertEquals(374469, $variant->getProduct()->getId());
            $this->assertEquals($product, $variant->getProduct());
        }
    }
}