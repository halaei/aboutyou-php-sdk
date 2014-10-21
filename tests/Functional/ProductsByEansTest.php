<?php
/**
 * @author nils.droege@project-collins.com
 * (c) ABOUT YOU GmbH
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;
use Collins\ShopApi\Criteria\ProductFields;

class ProductsByEansTest extends AbstractShopApiTest
{
    public function testFetchProductsByEans()
    {
        $shopApi = $this->getShopApiWithResultFile('result/products_eans.json');

        $productResult = $shopApi->fetchProductsByEans(array('4250671871492', '4250802292554'), array(ProductFields::VARIANTS));
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\ProductsEansResult', $productResult);
        $products = $productResult->getProducts();

        $this->assertCount(1, $products);

        foreach ($productResult as $product) {
            $this->assertInstanceOf('Collins\\ShopApi\\Model\\Product', $product);
        }

        $variants = $products[0]->getVariantsByEan('unknown');
        $this->assertInternalType('array', $variants);
        $this->assertEmpty($variants);

        $variants = $products[0]->getVariantsByEan('4250802292554');
        $this->assertCount(1, $variants);
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\Variant', $variants[0]);

        $errors = $productResult->getErrors();
        $this->assertCount(1, $errors);
        $this->assertInstanceOf('\stdClass', $errors[0]);
        $this->assertEquals(404, $errors[0]->error_code);
        $this->assertEquals('no such number', $errors[0]->error_message);

        $eansNotFound = $productResult->getEansNotFound();
        $this->assertCount(1, $eansNotFound);
        $this->assertEquals('4250671871492', $eansNotFound[0]);
    }
}
