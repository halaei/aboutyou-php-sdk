<?php

namespace Collins\ShopApi\Test\Live;

use Collins\ShopApi\Criteria\ProductFields;

class ProductTest extends \Collins\ShopApi\Test\Live\AbstractShopApiLiveTest
{
    /**
     * @group live
     */
    public function testGetCategories()
    {
        $product = $this->getProduct(1);
        
        $this->assertInternalType('array', $product->getRootCategories());
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