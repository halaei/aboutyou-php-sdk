<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;
use Collins\ShopApi\Model\Product;

class ProductAttributesTest extends ShopApiTest
{
    /** @var Product */
    private $product;

    /** @var ShopApi */
    private $shopApi;

    public function setup()
    {
        $json = json_decode(file_get_contents(__DIR__ . '/testData/product-with-attributes.json'));
        $this->product = new ShopApi\Model\Product($json);

        $this->shopApi = $this->getShopApiWithResultFile('facets-all.json');
    }

    public function testGetBrand()
    {
        $this->assertEquals(264, $this->product->getBrandId());

        $brand = $this->product->getBrand();

        $this->assertNotNull($brand);

        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Attribute', $brand);
        $this->assertEquals(0, $brand->getGroupId());
        $this->assertEquals(264, $brand->getId());
        $this->assertEquals('TOM TAILOR', $brand->getName());
        $this->assertEquals('brand', $brand->getGroupName());
    }

    public function testGetAttributes()
    {
        $attributes = $this->product->getAttributes();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\ProductAttributes', $attributes);

        $groups = $attributes->getGroups();
        $this->assertCount(4, $groups);

        $brand = $groups[ShopApi\Constants::FACET_BRAND];
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\AttributeGroup', $brand);
        $this->assertEquals(0, $brand->getId());
        $this->assertEquals('brand', $brand->getName());
        $attribute = reset($brand->getAttributes());

        $color = $groups[ShopApi\Constants::FACET_COLOR];
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\AttributeGroup', $color);
        $this->assertEquals(1, $color->getId());
        $this->assertEquals('color', $color->getName());
    }

    public function testGetGroupAttributes()
    {
        $colors = $this->product->getGroupAttributes(ShopApi\Constants::FACET_COLOR);
        $this->assertNotNull($colors);
        $this->assertInternalType('array', $colors);
        $color = $colors[12];
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Attribute', $color);
        $this->assertEquals(12, $color->getId());
        $this->assertEquals('Grau', $color->getName());
        $this->assertEquals('grau', $color->getValue());
        $this->assertEquals('color', $color->getGroupName());
    }
}
