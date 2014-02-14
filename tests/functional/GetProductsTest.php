<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class GetProductsTest extends ShopApiTest
{
    public function testFetchProducts()
    {
        $productIds = array(123, 456);

        $shopApi = $this->getShopApiWithResultFile('products.json');

        $productResult = $shopApi->fetchProductsByIds($productIds);
        $products = $productResult->getProducts();
        $this->assertCount(2, $products);
        $p123 = $products[123];
        $this->checkProduct($p123);
        $this->assertEquals(123, $p123->getId());
        $this->assertEquals('Product 1', $p123->getName());
        $this->assertTrue($p123->isActive()); // default is true!
        $this->assertFalse($p123->isSale());  // default is false!

        $p456 = $products[456];
        $this->checkProduct($p456);
        $this->assertEquals('Product 2', $p456->getName());
        $this->assertEquals(456, $p456->getId());

        return $productResult;
    }

    /**
     * @depends testFetchProducts
     */
    public function testProductResultIteratorInterface($productResult)
    {
        foreach ($productResult as $product) {
            $this->checkProduct($product);
        }
    }

    /**
     * @depends testFetchProducts
     */
    public function testProductResultArrayAccessInterface($productResult)
    {
        $this->checkProduct($productResult[123]);
        $this->checkProduct($productResult[456]);
    }

    /**
     * @depends testFetchProducts
     */
    public function testProductResultCountableInterface($productResult)
    {
        $this->assertCount(2, $productResult);
    }

    public function testFetchProductsAllFields()
    {
        $productIds = array(123, 456);

        $shopApi = $this->getShopApiWithResultFile('products-full.json');

        $productResult = $shopApi->fetchProductsByIds($productIds);
        $products = $productResult->getProducts();
        $this->assertCount(2, $products);

        $p123 = $products[123];
        $this->checkProduct($p123);
        $this->assertNull($p123->getDefaultImage());
        $this->assertFalse($p123->isActive());
        $this->assertFalse($p123->isSale());
        $this->assertEquals('description long 1', $p123->getDescriptionLong());
        $this->assertEquals('description short 1', $p123->getDescriptionShort());
        $c123Ids = $p123->getCategoryIds();
        $this->assertCount(5, $c123Ids);
        $this->assertEquals(19080, $c123Ids[0]);
        $this->assertEquals(123, $c123Ids[1]);
        $this->assertEquals(19096, $c123Ids[2]);

        $this->assertNull($p123->getDefaultVariant());

        $variants = $p123->getVariants();
        $this->assertCount(0, $variants);

        $p456 = $products[456];
        $this->checkProduct($p456);
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Image', $p456->getDefaultImage());
        $this->assertTrue($p456->isActive());
        $this->assertTrue($p456->isSale());

        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Variant', $p456->getDefaultVariant());

        $variants = $p456->getVariants();
        $this->assertCount(5, $variants);
        $variant = reset($variants);
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Variant', $variant);
        $this->assertEquals(5145543, $variant->getId());

        return $p456;
    }

    /**
     * @depends testFetchProductsAllFields
     */
    public function testFetchProductsWithAttributs($product)
    {
        $this->markTestIncomplete('The Method is not implemented yet');

        $attributes = $product->getAttributs();
    }

    /**
     * @depends testFetchProductsAllFields
     */
    public function testFetchProductsWithBrand($product)
    {
        $this->markTestIncomplete('The Method is not implemented yet');

        $attributes = $product->getBrand();
    }

    public function testFetchProductsWithStyles()
    {
        $productIds = array(220430);

        $shopApi = $this->getShopApiWithResultFile('products-with-styles.json');

        $productResult = $shopApi->fetchProductsByIds($productIds);
        $products = $productResult->getProducts();
        $this->assertCount(1, $products);

        $product = $products[220430];
        $styles  = $product->getStyles();
        $this->assertCount(5, $styles);
        foreach ($styles as $style) {
            $this->checkProduct($style);
            $this->assertNotEquals($product, $style);
        }
    }

    /**
     *
     */
    public function testSelectVariant()
    {
        $this->markTestIncomplete('The Method is not implemented yet');

        $productIds = array(123);

        $shopApi = $this->getShopApiWithResultFile('products.json');

        $productResult = $shopApi->fetchProductsByIds($productIds);
        $products = $productResult->getProducts();
        $product = $products[123];

        // if no variant is selected, return default variant
        $defaultVariant = $product->getDefaultVariant();
        $selectedVariant = $product->getSelectedVariant();
        $this->assertEquals($defaultVariant, $selectedVariant);

        // select specific variant
        $variantId = 111;
        $product->selectVariant($variantId);
        $selectedVariant = $product->getSelectedVariant();
        $this->assertNotEquals($defaultVariant, $selectedVariant);

        // select default variant
        $product->selectVariant(null);
        $selectedVariant = $product->getSelectedVariant();
        $this->assertEquals($defaultVariant, $selectedVariant);
    }

    /**
     *
     */
    public function testVariantImages()
    {
        $this->markTestIncomplete('The Method is not implemented yet');

        $productIds = array(123);

        $shopApi = $this->getShopApiWithResultFile('products-full.json');

        $productResult = $shopApi->fetchProductsByIds($productIds);
        $products = $productResult->getProducts();
        $product = $products[123];
        $variant = $product->getDefaultVariant();

        // select specific image
        $defaultImage = $variant->getImage();
        $imageHash = '2b0ee425a369b8feab3d1515a7bffaec';
        $variant->selectImage($imageHash);
        $selectedImage = $variant->getImage();
        $this->assertNotEquals($defaultImage, $selectedImage);
        $this->assertEquals($selectedImage, $variant->getImageByHash($imageHash));

        // select default image
        $variant->selectImage(null);
        $selectedImage = $variant->getImage();
        $this->assertEquals($defaultImage, $selectedImage);
    }

    private function checkProduct($product)
    {
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Product', $product);
        $this->assertObjectHasAttribute('id', $product);
        $this->assertObjectHasAttribute('name', $product);
    }
}
