<?php
namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class BasketTest extends AbstractShopApiTest
{
    public function testBasketProducts()
    {
        $shopApi = $this->getShopApiWithResultFile('basket.json');
        $basket = $shopApi->fetchBasket('12345');
        $products = $basket->getProducts();
        
        $this->assertEquals(count($products), 3);
        
        foreach ($products as $product) {
            $variants = $product->getVariants();
            foreach ($variants as $variant) {
                $quantity = $variant->getQuantity();
                $this->assertEquals(999, $quantity);
            }
        }
    }
}
