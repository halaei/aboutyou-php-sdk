<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;
use Collins\ShopApi\Model\Product;

class ProductGetCategoryTestAbstract extends AbstractShopApiTest
{
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

    public function testGetCategoryIdHierachies()
    {
        $json = $this->getJsonObjectFromFile('product/product-with-categories.json');
        $product = new ShopApi\Model\Product($json);

        $this->assertEquals(
            array(array(19080,123),array(19000),array(16080),array(19084),array(19097)),
            $product->getCategoryIdHierachies()
        );
    }

    public function testGetCategoryIdsEmpty()
    {
        $json = $this->getJsonObjectFromFile('product/product-with-attributes.json');
        $product = new ShopApi\Model\Product($json);

        $this->assertEquals(array(), $product->getCategoryIdHierachies());
    }

    public function testGetCategories()
    {
        $product = $this->getProduct('product-with-categories.json');
        $categories = $product->getCategories(false);
        $this->assertInternalType('array', $categories);
        $this->assertCount(4, $categories);
        foreach ($categories as $category) {
            $this->assertInstanceOf('Collins\\ShopApi\\Model\\Category', $category);
        }

        $product = $this->getProduct('product-with-categories.json');
        $categories = $product->getCategories();
        $this->assertInternalType('array', $categories);
        $this->assertCount(3, $categories);
        foreach ($categories as $category) {
            $this->assertInstanceOf('Collins\\ShopApi\\Model\\Category', $category);
        }
    }

    public function testGetFirstCategory()
    {
        $this->markTestIncomplete();
        $product = $this->getProduct('product-with-categories.json');
        $category = $product->getFirstCategory();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Category', $category);
        $this->assertEquals(19080, $category->getId());
    }

    public function testGetFirstActiveCategory()
    {
        $this->markTestIncomplete();
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
