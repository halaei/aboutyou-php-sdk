<?php

namespace Collins\ShopApi\Test\Live;


class ProductTest extends \Collins\ShopApi\Test\Live\AbstractShopApiLiveTest
{
    public function testGetCategories()
    {
        $product = $this->getProduct(1);
        
        $this->assertInternalType('array', $product->getCategories());
    }
}