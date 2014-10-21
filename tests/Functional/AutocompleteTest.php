<?php
namespace AboutYou\SDK\Test\Functional;

use \AY;

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
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Autocomplete', $autocomplete);

        foreach ($autocomplete->getProducts() as $product) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Product', $product);
        }

        foreach ($autocomplete->getCategories() as $category) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Category', $category);
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAutocompleteWithWrongSearchword()
    {
        $shopApi = $this->getShopApiWithResultFile(
            'result/autocompletion-shop.json'
        );
        
        $shopApi->fetchAutocomplete(false);
    }
}
