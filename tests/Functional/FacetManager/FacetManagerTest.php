<?php

namespace AboutYou\SDK\Test\Functional;

use \AY;
use AboutYou\SDK\Model\Basket;
use AboutYou\SDK\Model\ProductSearchResult;
use AboutYou\SDK\Model\FacetManager;

/**
 * @group facet-manager
 */
class FacetManagerTest extends AbstractShopApiTest
{
    protected $facetsResultPath = 'facets-for-product-variant-facets.json';

    public function testProductSearch()
    {
        $shopApi = $this->getShopApiWithResultFile(
            'product_search-result.json'
        );

        $productSearchResult = $shopApi->fetchProductSearch($shopApi->getProductSearchCriteria('12345'));
        $products = $productSearchResult->getProducts();

        $brand = $products[0]->getBrand();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Facet', $brand);
    }

    public function testProductByEans()
    {
        $shopApi = $this->getShopApiWithResultFile(
            'products_eans-result.json'
        );

        $productEansResult = $shopApi->fetchProductsByEans(array('dummy'));
        $products = $productEansResult->getProducts();

        $brand = $products[0]->getBrand();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Facet', $brand);
    }

    public function testProductByIds()
    {
        $shopApi = $this->getShopApiWithResultFile(
            'products-result.json'
        );

        $productResult = $shopApi->fetchProductsByIds(array('dummy'));
        $products      = $productResult->getProducts();

        $brand = $products[301673]->getBrand();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Facet', $brand);
    }

    public function testAutocomplete()
    {
        $shopApi = $this->getShopApiWithResultFile(
            'autocompletion-result.json'
        );

        $autocompletionResult = $shopApi->fetchAutocomplete('dummy');
        $products = $autocompletionResult->getProducts();

        $brand = $products[0]->getBrand();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Facet', $brand);
    }

    public function testBasket()
    {
        $shopApi = $this->getShopApiWithResultFile(
            'basket-result.json'
        );

        $basket = $shopApi->fetchBasket('dummy');
        $products = $basket->getProducts();
        $product = reset($products);

        $brand = $product->getBrand();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Facet', $brand);
    }

    public function testGetOrder()
    {
        $shopApi = $this->getShopApiWithResultFile(
            'get_order-result.json'
        );

        $order = $shopApi->fetchOrder('dummy');
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