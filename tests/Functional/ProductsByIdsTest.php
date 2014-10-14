<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class ProductsByIdsTest extends AbstractShopApiTest
{
    public function testFetchProducts()
    {
        $productIds = array(123, 456);

        $shopApi = $this->getShopApiWithResultFile('result/products.json');

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

        $shopApi = $this->getShopApiWithResultFile('result/products-full.json');

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
        $c123Ids = $p123->getCategoryIdHierachies();
        $this->assertCount(4, $c123Ids);

        $this->assertEquals(19080, $c123Ids[0][0]);
        $this->assertEquals(123, $c123Ids[0][1]);
        $this->assertEquals(19084, $c123Ids[2][0]);

        $this->assertNull($p123->getDefaultVariant());

        $variants = $p123->getVariants();
        $this->assertCount(0, $variants);

        $p456 = $products[456];
        $this->checkProduct($p456);
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Image', $p456->getDefaultImage());
        $this->assertTrue($p456->isActive());
        $this->assertTrue($p456->isSale());
        $this->assertEquals(3980, $p456->getMinPrice());
        $this->assertEquals(3990, $p456->getMaxPrice());

        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Variant', $p456->getDefaultVariant());

        $variants = $p456->getVariants();
        $this->assertCount(5, $variants);
        $variant = reset($variants);
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Variant', $variant);
        $this->assertEquals(5145543, $variant->getId());

        return $p456;
    }

    public function testFetchProductsSample()
    {
        $productIds = array(123, 456);

        $shopApi = $this->getShopApiWithResultFile('p.json');

        $productResult = $shopApi->fetchProductsByIds($productIds);
        $products = $productResult->getProducts();

        $product = reset($products);
        $this->checkProductFull($product);
    }


    public function testFetchProductsWithStyles()
    {
        $productIds = array(220430);

        $shopApi = $this->getShopApiWithResultFile('result/products-with-styles.json');

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

    public function testProductNotFound()
    {
        $result = <<<EOS
[
    {
        "products": {
            "pageHash": "2163505b-0083-44b6-b547-b564ae463328",
            "ids": {
                "1": { "error_message": [ "product not found" ], "error_code": 404 },
                "123": { "active": false, "styles": [], "id": 123, "name": "Product 123" }
            }
        }
    }
]
EOS;
        $shopApi = $this->getShopApiWithResult($result);

        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
        $logger->expects($this->once())
            ->method('warning')
        ;
        $shopApi->setLogger($logger);

        $productResult = $shopApi->fetchProductsByIds(array(1, 123));
        $products = $productResult->getProducts();
        $this->assertCount(1, $products);
        $product = reset($products);
        $this->assertEquals(123, $product->getId());

        $errors = $productResult->getErrors();
        $this->assertCount(1, $errors);
        $this->assertInstanceOf('\stdClass', $errors[0]);
        $this->assertEquals(404, $errors[0]->error_code);
        $this->assertEquals('product not found', $errors[0]->error_message[0]);

        $this->assertEquals(1, $productResult->getProductsNotFound()[0]);
    }

    /**
     *
     */
    public function testVariantImages()
    {
        $productIds = array(456);

        $shopApi = $this->getShopApiWithResultFile('result/products-full.json');

        $productResult = $shopApi->fetchProductsByIds($productIds);
        $products = $productResult->getProducts();
        $product = $products[456];
        $variant = $product->getDefaultVariant();

        $defaultImage = $variant->getImage();
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Image', $defaultImage);
        $this->assertEquals('882ff9a8365b6e1b46773992b189e4dc', $defaultImage->getHash());
    }

    private function checkProduct($product)
    {
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\\Product', $product);
        $this->assertObjectHasAttribute('id', $product);
        $this->assertObjectHasAttribute('name', $product);
    }

    private function checkProductFull(ShopApi\Model\Product $product)
    {
        $this->checkProduct($product);
        $variants = $product->getVariants();
        if ($variants === null) {
            $this->fail('a Product must have at least one variant');
        } else {
            foreach ($variants as $variant) {
                $this->checkVariant($variant);
            }
        }
    }

    private function checkVariant(ShopApi\Model\Variant $variant)
    {
        $this->assertInternalType('int', $variant->getId());
        $this->assertGreaterThan(0, count($variant->getFacetIds()));
    }
}
