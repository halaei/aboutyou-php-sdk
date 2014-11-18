<?php

namespace AboutYou\SDK\Test\Live;


use AboutYou\SDK\Constants;
use AboutYou\SDK\Model\Autocomplete;

/**
 * @group live
 */
class AutocompleteTest extends \AboutYou\SDK\Test\Live\AbstractAYLiveTest
{
    public function testAutocomplete()
    {
        $ay = $this->getAY();

        $autocomplete = $ay->fetchAutocomplete('damen', 1);
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Autocomplete', $autocomplete);
        $products = $autocomplete->getProducts();
        $this->assertGreaterThan(0, $products);

        foreach ($products as $product) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Product', $product);
        }

        $categories = $autocomplete->getCategories();
        $this->assertGreaterThan(0, $categories);
        foreach ($categories as $category) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Category', $category);
        }

        $autocomplete = $ay->fetchAutocomplete('not existent', 10);
        $this->assertCount(0, $autocomplete->getProducts());
        $this->assertCount(0, $autocomplete->getCategories());

        $autocomplete = $ay->fetchAutocomplete('damen', 2, array(Constants::TYPE_PRODUCTS));
        $this->assertEquals(Autocomplete::NOT_REQUESTED, $autocomplete->getCategories());
        $this->assertEquals(Autocomplete::NOT_REQUESTED, $autocomplete->getBrands());
        $this->assertCount(2, $autocomplete->getProducts());

        $autocomplete = $ay->fetchAutocomplete('damen', 1, array(Constants::TYPE_CATEGORIES));
        $this->assertEquals(Autocomplete::NOT_REQUESTED, $autocomplete->getProducts());
        $this->assertEquals(Autocomplete::NOT_REQUESTED, $autocomplete->getBrands());
        $this->assertCount(1, $autocomplete->getCategories());
        
        $autocomplete = $ay->fetchAutocomplete('Tama', 1, array(Constants::TYPE_BRANDS));
        $this->assertEquals(Autocomplete::NOT_REQUESTED, $autocomplete->getProducts());
        $this->assertEquals(Autocomplete::NOT_REQUESTED, $autocomplete->getCategories());
        $this->assertCount(1, $autocomplete->getBrands());

        $autocomplete = $ay->fetchAutocomplete('Damen', 1);
        $this->assertCount(1, $autocomplete->getProducts());
        $this->assertCount(1, $autocomplete->getCategories());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFetchAutocompleteWithInt()
    {
        $ay = $this->getAY();
        $autocomplete = $ay->fetchAutocomplete(false, 10);
    }

    public function testUmlaut()
    {
        $ay = $this->getAY();
        $autocomplete = $ay->fetchAutocomplete('Gürtel');

        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Autocomplete', $autocomplete);
    }
}