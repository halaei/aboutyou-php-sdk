<?php

namespace Collins\ShopApi\Test\Live;


class AutocompelteTest extends \Collins\ShopApi\Test\Live\AbstractShopApiLiveTest
{

    /**
     * @group live
     */
    public function testAutocomplete()
    {
        $shopApi = $this->getShopApi();

        $autocomplete = $shopApi->fetchAutocomplete('Shop', 10);
        $this->assertInstanceOf('Collins\ShopApi\Model\Autocomplete', $autocomplete);

        foreach ($autocomplete->getProducts() as $product) {
            $this->assertInstanceOf('Collins\ShopApi\Model\Product', $product);
        }

        foreach ($autocomplete->getCategories() as $category) {
            $this->assertInstanceOf('Collins\ShopApi\Model\Category', $category);
        }      
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