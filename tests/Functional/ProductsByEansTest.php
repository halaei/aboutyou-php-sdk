<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class ProductsByEansTest extends AbstractShopApiTest
{
    public function testFetchProductsByEans()
    {
        $shopApi = $this->getShopApiWithResultFile('result/products_eans.json');

        $productResult = $shopApi->fetchProductsByEans(array('dummy ean'));
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\ProductsEansResult', $productResult);
        $products = $productResult->getProducts();

        $this->assertCount(2, $products);

        foreach ($productResult as $product) {
            $this->assertInstanceOf('Collins\\ShopApi\\Model\\Product', $product);
        }

        $variants = $products[0]->getVariantsByEan('unknown');
        $this->assertInternalType('array', $variants);
        $this->assertEmpty($variants);

        $variants = $products[0]->getVariantsByEan('8806159322381');
        $this->assertCount(1, $variants);
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Variant', $variants[0]);
    }
}
