<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;
use Collins\ShopApi\Model\Product;

class ProductGetCategoryTestAbstract extends AbstractShopApiTest
{
    /** @var Product */
    private $product;

    /** @var ShopApi */
    private $shopApi;

    public function setup()
    {
        $this->shopApi = $this->getShopApiWithResultFile('category.json');
    }

    public function getProduct($filname)
    {
        $json = $this->getJsonObjectFromFile('product/' . $filname);
        $product = new ShopApi\Model\Product($json);

        return $product;
    }

    public function testGetCategoryIdPaths()
    {
        $json = $this->getJsonObjectFromFile('product/product-with-categories.json');
        $product = new ShopApi\Model\Product($json);

        $this->assertEquals(
            'a:5:{i:0;a:2:{i:0;i:19080;i:1;i:123;}i:1;a:1:{i:0;i:19000;}i:2;a:1:{i:0;i:16080;}i:3;a:1:{i:0;i:19084;}i:4;a:1:{i:0;i:19097;}}',
            serialize($product->getCategoryIdPaths())
        );
    }

    public function testGetCategoryIdsEmpty()
    {
        $json = $this->getJsonObjectFromFile('product/product-with-attributes.json');
        $product = new ShopApi\Model\Product($json);

        $this->assertEquals([], $product->getCategoryIdPaths());
    }

    public function testGetCategories()
    {
        $product = $this->getProduct('product-with-categories.json');
        $categories = $product->getCategories();
        $this->assertInternalType('array', $categories);
        $this->assertCount(4, $categories);
        foreach ($categories as $category) {
            $this->assertInstanceOf('Collins\\ShopApi\\Model\\Category', $category);
        }
    }

    public function testGetFirstCategory()
    {
        $product = $this->getProduct('product-with-categories.json');
        $category = $product->getFirstCategory();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Category', $category);
        $this->assertEquals(19080, $category->getId());
    }

    public function testGetFirstActiveCategory()
    {
        $product = $this->getProduct('product-with-categories.json');
        $category = $product->getFirstActiveCategory();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Category', $category);
        $this->assertEquals(16080, $category->getId());
    }

    public function testGetCategory()
    {
        $product = $this->getProduct('product-with-categories.json');
        $category = $product->getCategory();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Category', $category);
        $this->assertEquals(16080, $category->getId());
    }

    public function testGetCategoryNull()
    {
        $product  = $this->getProduct('product-with-attributes.json');
        $category = $product->getCategory();
        $this->assertNull($category);
    }
}
