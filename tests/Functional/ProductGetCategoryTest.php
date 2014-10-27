<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Functional;

use AboutYou\SDK\Model\Product;

class ProductGetCategoryTest extends AbstractAYTest
{
    protected $setupCategoryManager = false;

    /** @var \AY */
    private $ay;

    public function setup()
    {
        $this->ay = $this->getAYWithResultFile('product/category.json');
        $this->ay->getCategoryManager(true);
    }

    public function getProduct($filename)
    {
        $json = $this->getJsonObjectFromFile('product/' . $filename);
        $product = Product::createFromJson($json, $this->ay->getResultFactory(), 98);

        return $product;
    }

    public function testGetCategoryIdHierachies()
    {
        $product = $this->getProduct('product-with-categories.json');

        $this->assertEquals(
            array(array(2,21),array(1,12,121),array(1,11),array(3)),
            $product->getCategoryIdHierachies()
        );
    }

    public function testGetCategoryIdsEmpty()
    {
        $product = $this->getProduct('product-with-attributes.json');

        $this->assertEquals(array(), $product->getCategoryIdHierachies());
    }

    public function testGetCategories()
    {
        $product = $this->getProduct('product-with-categories.json');
        $categories = $product->getRootCategories(false);
        $this->assertInternalType('array', $categories);
        $this->assertCount(3, $categories);

        foreach ($categories as $category) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Category', $category);
        }
        $this->assertEquals(2, array_shift($categories)->getId());
        $this->assertEquals(1, array_shift($categories)->getId());
        $this->assertEquals(3, array_shift($categories)->getId());
    }

    public function testGetLeafCategories()
    {
        $product = $this->getProduct('product-with-categories.json');
        $categories = $product->getLeafCategories(false);
        $this->assertInternalType('array', $categories);

        $this->assertCount(4, $categories);
        foreach ($categories as $category) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Category', $category);
        }
        $this->assertEquals(21, array_shift($categories)->getId());
        $this->assertEquals(121, array_shift($categories)->getId());
        $this->assertEquals(11, array_shift($categories)->getId());
        $this->assertEquals(3, array_shift($categories)->getId());
    }

    public function testGetCategory()
    {
        $product = $this->getProduct('product-with-categories.json');
        $category = $product->getCategory(false);
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Category', $category);
        $this->assertEquals(21, $category->getId());

        $category = $product->getCategory();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Category', $category);
        $this->assertEquals(21, $category->getId());
    }

    public function testGetCategoryWithLongestActivePath()
    {
        $product = $this->getProduct('product-with-categories.json');
        $category = $product->getCategoryWithLongestActivePath();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Category', $category);
        $this->assertEquals(121, $category->getId());
    }

    public function testGetCategoryNull()
    {
        $product  = $this->getProduct('product-with-attributes.json');
        $category = $product->getCategory();
        $this->assertNull($category);
    }
}
