<?php
namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class AutocompleteTest extends ShopApiTest
{
    /**
     *
     */
    public function testAutocomplete()
    {
        $shopApi = $this->getShopApiWithResultFile('autocomplete-sho.json');

        $autocomplete = $shopApi->fetchAutocomplete('Sho', 10);
        $this->assertInstanceOf('Collins\ShopApi\Model\Autocomplete', $autocomplete);

        foreach ($autocomplete->getProducts() as $product) {
            $this->assertInstanceOf('Collins\ShopApi\Model\Product', $product);
        }

        foreach ($autocomplete->getCategories() as $category) {
            $this->assertInstanceOf('Collins\ShopApi\Model\Category', $category);
        }
    }
}
