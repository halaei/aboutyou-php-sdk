<?php

namespace Collins\ShopApi\Test\Live;


use Collins\ShopApi\Constants;
use Collins\ShopApi\Model\Autocomplete;

class AutocompelteTest extends \Collins\ShopApi\Test\Live\AbstractShopApiLiveTest
{

    /**
     * @group live
     */
    public function testAutocomplete()
    {
        $shopApi = $this->getShopApi();

        $autocomplete = $shopApi->fetchAutocomplete('damen', 1);
        $this->assertInstanceOf('Collins\ShopApi\Model\Autocomplete', $autocomplete);
        $products = $autocomplete->getProducts();
        $this->assertGreaterThan(0, $products);

        foreach ($products as $product) {
            $this->assertInstanceOf('Collins\ShopApi\Model\Product', $product);
        }

        $categories = $autocomplete->getCategories();
        $this->assertGreaterThan(0, $categories);
        foreach ($categories as $category) {
            $this->assertInstanceOf('Collins\ShopApi\Model\Category', $category);
        }

        $autocomplete = $shopApi->fetchAutocomplete('not existent', 10);
        $this->assertCount(0, $autocomplete->getProducts());
        $this->assertCount(0, $autocomplete->getCategories());

        $autocomplete = $shopApi->fetchAutocomplete('damen', 2, array(Constants::TYPE_PRODUCTS));
        $this->assertCount(2, $autocomplete->getProducts());
        $this->assertEquals(Autocomplete::NOT_REQUESTED, $autocomplete->getCategories());

        $autocomplete = $shopApi->fetchAutocomplete('damen', 1, array(Constants::TYPE_CATEGORIES));
        $this->assertEquals(Autocomplete::NOT_REQUESTED, $autocomplete->getProducts());
        $this->assertCount(1, $autocomplete->getCategories());
    }
    
    /**
     * @group live     
     * @expectedException \InvalidArgumentException
     */
    public function testFetchAutocompleteWithInt()
    {
        $shopApi = $this->getShopApi();
        $autocomplete = $shopApi->fetchAutocomplete(false, 10);        
    }
}