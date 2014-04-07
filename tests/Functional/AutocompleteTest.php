<?php
namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class AutocompleteTest extends AbstractShopApiTest
{
    /**
     *
     */
    public function testAutocomplete()
    {
        $shopApi = $this->getShopApiWithResultFile(
            'result/autocompletion-shop.json'
        );

        $autocomplete = $shopApi->fetchAutocomplete('Shop', 10);
        $this->assertInstanceOf('Collins\ShopApi\Model\Autocomplete', $autocomplete);

        foreach ($autocomplete->getProducts() as $product) {
            $this->assertInstanceOf('Collins\ShopApi\Model\Product', $product);
        }

        foreach ($autocomplete->getCategories() as $category) {
            $this->assertInstanceOf('Collins\ShopApi\Model\Category', $category);
        }
    }
}
