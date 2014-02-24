<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;
use Collins\ShopApi\Model\Product;

class ProductGetCategoryTest extends ShopApiTest
{
    /** @var Product */
    private $product;

    /** @var ShopApi */
    private $shopApi;

    public function getProduct($filname)
    {
        $json = $this->getJsonObjectFromFile('product/' . $filname);
        $product = new ShopApi\Model\Product($json);

        $shopApi = $this->getShopApiWithResultFile('category.json');
        Product::setShopApi($shopApi);

        return $product;
    }

    public function testGetCategoryIds()
    {
        $json = $this->getJsonObjectFromFile('product/product-with-categories.json');
        $product = new ShopApi\Model\Product($json);

        $this->assertEquals([19080,123,16080,19084,19097], $product->getCategoryIds());
    }

    public function testGetCategoryIdsEmpty()
    {
        $json = $this->getJsonObjectFromFile('product/product-with-attributes.json');
        $product = new ShopApi\Model\Product($json);

        $this->assertEquals([], $product->getCategoryIds());
    }

    public function testGetCategory()
    {
        $product = $this->getProduct('product-with-categories.json');
        $category = $product->getMainCategory();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Category', $category);
        $this->assertEquals(16080, $category->getId());
    }

    public function testGetCategoryNull()
    {
        $product  = $this->getProduct('product-with-attributes.json');
        $category = $product->getMainCategory();
        $this->assertNull($category);
    }
}
