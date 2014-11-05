<?php

namespace AboutYou\SDK\Test\Functional;

use \AY;
use AboutYou\SDK\Model\Basket;
use AboutYou\SDK\Model\ProductSearchResult;
use AboutYou\SDK\Model\FacetManager;

/**
 * @group facet-manager
 */
class FacetManagerTest extends AbstractAYTest
{
    protected $facetsResultPath = 'facets-for-product-variant-facets.json';

    public function testProductSearch()
    {
        $ay = $this->getAYWithResultFile(
            'product_search-result.json'
        );

        $productSearchResult = $ay->fetchProductSearch($ay->getProductSearchCriteria('12345'));
        $products = $productSearchResult->getProducts();

        $brand = $products[0]->getBrand();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Facet', $brand);
    }

    public function testProductByEans()
    {
        $ay = $this->getAYWithResultFile(
            'products_eans-result.json'
        );

        $productEansResult = $ay->fetchProductsByEans(array('dummy'));
        $products = $productEansResult->getProducts();

        $brand = $products[0]->getBrand();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Facet', $brand);
    }

    public function testProductByIds()
    {
        $ay = $this->getAYWithResultFile(
            'products-result.json'
        );

        $productResult = $ay->fetchProductsByIds(array('dummy'));
        $products      = $productResult->getProducts();

        $brand = $products[301673]->getBrand();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Facet', $brand);
    }

    public function testAutocomplete()
    {
        $ay = $this->getAYWithResultFile(
            'autocompletion-result.json'
        );

        $autocompletionResult = $ay->fetchAutocomplete('dummy');
        $products = $autocompletionResult->getProducts();

        $brand = $products[0]->getBrand();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Facet', $brand);
    }

    public function testBasket()
    {
        $ay = $this->getAYWithResultFile(
            'basket-result.json'
        );

        $basket = $ay->fetchBasket('dummy');
        $products = $basket->getProducts();
        $product = reset($products);

        $brand = $product->getBrand();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Facet', $brand);
    }

    public function testGetOrder()
    {
        $ay = $this->getAYWithResultFile(
            'get_order-result.json'
        );

        $order = $ay->fetchOrder('dummy');
        $products = $order->getBasket()->getProducts();
        $product = reset($products);

        $brand = $product->getBrand();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Facet', $brand);
    }

    protected function getJsonStringFromFile($filepath)
    {
        return parent::getJsonStringFromFile($filepath, __DIR__);
    }
}