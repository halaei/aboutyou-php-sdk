<?php

namespace AboutYou\SDK\Test\Live;

use AboutYou\SDK\Constants;
use AboutYou\SDK\Criteria\ProductFields;
use AboutYou\SDK\Model\Product;

/**
 * @group live
 */
class ProductTest extends \AboutYou\SDK\Test\Live\AbstractAYLiveTest
{
    public function xtestGetProduct()
    {
        $product = $this->getProduct(1, array(
            ProductFields::MIN_PRICE,
            ProductFields::MAX_PRICE,
            ProductFields::VARIANTS
        ));
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\Product', $product);

        return $product;
    }

    /**
     * @depends testGetProduct
     */
    public function xtestGetCategories(Product $product)
    {
        $this->assertInternalType('array', $product->getRootCategories());
    }

    /**
     * @depends testGetProduct
     */
    public function xtestGetMxxPrice(Product $product)
    {
        $this->assertNotNull($product->getMinPrice());
        $this->assertInternalType('int', $product->getMinPrice());
        $this->assertNotNull($product->getMaxPrice());
        $this->assertInternalType('int', $product->getMaxPrice());
    }

    public function xtestGetProductFull()
    {
        $product = $this->getProduct(1, array(
            ProductFields::ATTRIBUTES_MERGED,
            ProductFields::BRAND,
            ProductFields::BULLET_POINTS,
            ProductFields::CATEGORIES,
            ProductFields::DEFAULT_IMAGE,
            ProductFields::DEFAULT_VARIANT,
            ProductFields::DESCRIPTION_LONG,
            ProductFields::DESCRIPTION_SHORT,
            ProductFields::INACTIVE_VARIANTS,
            ProductFields::IS_ACTIVE,
            ProductFields::IS_SALE,
            ProductFields::MIN_PRICE,
            ProductFields::MAX_PRICE,
            ProductFields::MAX_SAVINGS,
            ProductFields::MAX_SAVINGS_PERCENTAGE,
            ProductFields::VARIANTS,
            'merchant_id'
        ));
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\Product', $product);

        $this->assertInternalType('int', $product->getMaxSavingsPrice());
        $this->assertInternalType('float', $product->getMaxSavingsPercentage());
        $this->assertInternalType('int', $product->getMerchantId());
        $bulletPoints = $product->getBulletPoints();
        $this->assertInternalType('array', $bulletPoints);
        foreach ($bulletPoints as $bulletPoint) {
            $this->assertInternalType('string', $bulletPoint);
        }

        $variants = $product->getInactiveVariants();
        if ($variants !== null) {
            $this->assertInternalType('array', $variants);
            foreach ($variants as $variant) {
                $this->assertInstanceOf('\\AboutYou\\SDK\\Model\Variant', $variant);
            }
        }

        return $product;
    }

    public function xtestSingleProduct()
    {
        $ay = new \AY(303, 'dd898b91611b11f31e8d60c1bffab138');

        $result = $ay->fetchProductsByIds([1964583], [ProductFields::CATEGORIES, ProductFields::VARIANTS]);

        $products = $result->getProducts();
        $product = array_shift($products);
        $this->assertEquals(1964583, $product->getId());
    }

    public function xtestSingleProductFromEdited()
    {
        $ay = new \AY(53, 'h]vWu6PAuz7sfdYNZ5VqkfM^93W0k{3m');

        $result = $ay->fetchProductsByIds([1966800], [
            ProductFields::CATEGORIES,
            ProductFields::VARIANTS
        ]);

        $products = $result->getProducts();

        $this->assertEquals(1, count($products));

        $product = array_shift($products);
        $categories = $product->getLeafCategories();

        foreach ($categories as $category) {
            $breadCrumb = json_encode(array_map(function($category) {
                return $category->getName();
            }, $category->getBreadcrumb()));
        }
    }

    public function xtestFirstPublicationDate()
    {
        $api = $this->getAY();
        $result = $api->fetchProductsByIds([556226]);

        $products = $result->getProducts();

        $this->assertCount(1, $products);

        $product = array_shift($products);

        $this->assertInstanceOf('\DateTime', $product->getFirstPublicationDate());
        $this->assertEquals('26.08.2014', $product->getFirstPublicationDate()->format('d.m.Y'));
    }


    public function xtestSizeAdvice()
    {
        $api = $this->getAY();
        $result = $api->fetchProductsByIds([556226], [ProductFields::ATTRIBUTES_MERGED, ProductFields::PRODUCT_ATTRIBUTES]);

        $products = $result->getProducts();

        $this->assertCount(1, $products);

        $product = array_shift($products);

        $sizeAdvice = $product->getSizeAdvice();

        $this->assertEquals('one_unit_smaller', $sizeAdvice->getValue());

    }

    public function testProductFetch()
    {
        foreach ([1578627, 1902746] as $productId) {
            for ($i=0; $i<20; $i++) {
                $start = microtime(true);

                $api = $this->getAY();
                $result = $api->fetchProductsByIds([$productId], [
                    "id",
                    "name",
                    "active",
                    "brand_id",
                    "description_long",
                    "description_short",
                    "default_variant",
                    "variants",
                    "min_price",
                    "max_price",
                    "sale",
                    "default_image",
                    "attributes_merged",
                    "categories"
                ], false);
                $products = $result->getProducts();
                $product = array_shift($products);
                $end = microtime(true);

                $diff = $end-$start;

                echo sprintf($product->getName().' - %.16f', $diff).'ms'.PHP_EOL;
            }
        }
    }
}