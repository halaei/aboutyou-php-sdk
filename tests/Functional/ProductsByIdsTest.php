<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Functional;

use AboutYou\SDK\Criteria\ProductFields;
use AboutYou\SDK\Model;

class ProductsByIdsTest extends AbstractAYTest
{
    public function testFetchProducts()
    {
        $productIds = array(123, 456);

        $ay = $this->getAYWithResultFile('result/products.json');

        $productResult = $ay->fetchProductsByIds($productIds);
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

        $ay = $this->getAYWithResultFile('result/products-full.json');

        $productResult = $ay->fetchProductsByIds($productIds);
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
        $attributes = $p123->getAttributes();
        $this->assertInternalType('array', $attributes);
        $this->arrayHasKey('443', $attributes);
        $this->assertInternalType('array', $attributes['443']);
        $this->assertCount(1, $attributes['443']);
        foreach ($attributes['443'] as $attribute) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Facet', $attribute);
        }

        $variants = $p123->getVariants();
        $this->assertCount(0, $variants);

        $bulletPoints = $p123->getBulletPoints();
        $this->assertInternalType('array', $bulletPoints);
        $this->assertCount(6, $bulletPoints);
        foreach ($bulletPoints as $bulletPoint) {
            $this->assertInternalType('string', $bulletPoint);
        }

        $p456 = $products[456];
        $this->checkProduct($p456);
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Image', $p456->getDefaultImage());
        $this->assertTrue($p456->isActive());
        $this->assertTrue($p456->isSale());
        $this->assertEquals(3980, $p456->getMinPrice());
        $this->assertEquals(3990, $p456->getMaxPrice());
        $this->assertNull($p456->getAttributes());

        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Variant', $p456->getDefaultVariant());

        $variants = $p456->getVariants();
        $this->assertCount(5, $variants);
        $variant = reset($variants);
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Variant', $variant);
        $this->assertEquals(5145543, $variant->getId());

        return $p456;
    }

    public function testFetchProductsSample()
    {
        $productIds = array(123, 456);

        $ay = $this->getAYWithResultFile('p.json');

        $productResult = $ay->fetchProductsByIds($productIds);
        $products = $productResult->getProducts();

        $product = reset($products);
        $this->checkProductFull($product);
    }


    public function testFetchProductsWithStyles()
    {
        $productIds = array(220430);

        $ay = $this->getAYWithResultFile('result/products-with-styles.json');

        $productResult = $ay->fetchProductsByIds($productIds);
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
        $ay = $this->getAYWithResult($result);

        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');
        $logger->expects($this->once())
            ->method('warning')
        ;
        $ay->setLogger($logger);

        $productResult = $ay->fetchProductsByIds(array(1, 123));
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
    public function testProductImages()
    {
        $ay = $this->getAYWithResultFile('result/products-full.json');

        $productResult = $ay->fetchProductsByIds([456]);
        $products = $productResult->getProducts();
        $product = $products[456];

        $defaultImage = $product->getImage();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Image', $defaultImage);
        $this->assertEquals('882ff9a8365b6e1b46773992b189e4dc', $defaultImage->getHash());
    }

    public function testQueryBuilder()
    {
        $productIds = array(456);

        $ay = $this->getAYWithResultFile('result/products.json', '[{"products":{"ids":[456],"fields":["default_image","new_in_since_date"],"get_styles":false}}]');

        $ay->fetchProductsByIds($productIds, array(ProductFields::DEFAULT_IMAGE), false);
    }

    private function checkProduct($product)
    {
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Product', $product);
        $this->assertObjectHasAttribute('id', $product);
        $this->assertObjectHasAttribute('name', $product);
    }

    private function checkProductFull(Model\Product $product)
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

    private function checkVariant(Model\Variant $variant)
    {
        $this->assertInternalType('int', $variant->getId());
        $this->assertGreaterThan(0, count($variant->getFacetIds()));
    }
}
