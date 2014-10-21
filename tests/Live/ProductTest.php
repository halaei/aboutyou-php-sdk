<?php

namespace AboutYou\SDK\Test\Live;

use AboutYou\SDK\Criteria\ProductFields;
use AboutYou\SDK\Model\Product;

/**
 * @group live
 */
class ProductTest extends \AboutYou\SDK\Test\Live\AbstractShopApiLiveTest
{
    public function testGetProduct()
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
    public function testGetCategories(Product $product)
    {
        $this->assertInternalType('array', $product->getRootCategories());
    }

    /**
     * @depends testGetProduct
     */
    public function testGetMxxPrice(Product $product)
    {
        $this->assertNotNull($product->getMinPrice());
        $this->assertInternalType('int', $product->getMinPrice());
        $this->assertNotNull($product->getMaxPrice());
        $this->assertInternalType('int', $product->getMaxPrice());
    }

    public function testGetProductFull()
    {
        $product = $this->getProduct(1, array(
            ProductFields::ATTRIBUTES_MERGED,
            ProductFields::BRAND,
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
        $this->assertInternalType('int', $product->getMaxSavingsPercentage());
        $this->assertInternalType('int', $product->getMerchantId());

        $variants = $product->getInactiveVariants();
        if ($variants !== null) {
            $this->assertInternalType('array', $variants);
            foreach ($variants as $variant) {
                $this->assertInstanceOf('\\AboutYou\\SDK\\Model\Variant', $variant);
            }
        }

        return $product;
    }
}