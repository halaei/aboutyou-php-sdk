<?php

namespace Collins\ShopApi\Test\Live;

use Collins\ShopApi\Criteria\ProductFields;
use Collins\ShopApi\Model\Product;

/**
 * @group live
 */
class ProductTest extends \Collins\ShopApi\Test\Live\AbstractShopApiLiveTest
{
    public function testGetProduct()
    {
        $product = $this->getProduct(1, array(
            ProductFields::MIN_PRICE,
            ProductFields::MAX_PRICE,
            ProductFields::VARIANTS
        ));
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\Product', $product);

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
        $this->assertInstanceOf('\\Collins\\ShopApi\\Model\Product', $product);

        $this->assertInternalType('int', $product->getMaxSavingsPrice());
        $this->assertInternalType('int', $product->getMaxSavingsPercentage());
        $this->assertInternalType('int', $product->getMerchantId());

        $variants = $product->getInactiveVariants();
        if ($variants !== null) {
            $this->assertInternalType('array', $variants);
            foreach ($variants as $variant) {
                $this->assertInstanceOf('\\Collins\\ShopApi\\Model\Variant', $variant);
            }
        }

        return $product;
    }

//      TODO: search for a product with no length, eg. shows
//    public function testGetVariantGetLength()
//    {
//        $id = 402573;
//        $api = $this->getShopApi();
//
//        $productsResult = $api->fetchProductsByIds([$id], array(ProductFields::VARIANTS));
//        $product = $productsResult[$id];
//        $variants = $product->getVariants();
//        $variant = reset($variants);
//
//        $this->assertNull($variant->getLength());
//    }
}