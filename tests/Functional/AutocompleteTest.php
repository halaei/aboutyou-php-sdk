<?php
namespace AboutYou\SDK\Test\Functional;

use \AY;

class AutocompleteTest extends AbstractAYTest
{
    /**
     *
     */
    public function testAutocomplete()
    {
        $ay = $this->getAYWithResultFile(
            'result/autocompletion-shop.json'
        );

        $autocomplete = $ay->fetchAutocomplete('Shop', 10);
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Autocomplete', $autocomplete);

        foreach ($autocomplete->getProducts() as $product) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Product', $product);
        }

        foreach ($autocomplete->getCategories() as $category) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Category', $category);
        }

        foreach ($autocomplete->getBrands() as $brand) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Brand', $brand);
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAutocompleteWithWrongSearchword()
    {
        $ay = $this->getAYWithResultFile(
            'result/autocompletion-shop.json'
        );
        
        $ay->fetchAutocomplete(false);
    }
}
