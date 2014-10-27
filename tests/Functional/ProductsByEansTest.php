<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Functional;

use \AY;
use AboutYou\SDK\Criteria\ProductFields;

class ProductsByEansTest extends AbstractAYTest
{
    public function testFetchProductsByEans()
    {
        $ay = $this->getAYWithResultFile('result/products_eans.json');

        $productResult = $ay->fetchProductsByEans(array('4250671871492', '4250802292554'), array(ProductFields::VARIANTS));
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\ProductsEansResult', $productResult);
        $products = $productResult->getProducts();

        $this->assertCount(1, $products);

        foreach ($productResult as $product) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Product', $product);
        }

        $variants = $products[0]->getVariantsByEan('unknown');
        $this->assertInternalType('array', $variants);
        $this->assertEmpty($variants);

        $variants = $products[0]->getVariantsByEan('4250802292554');
        $this->assertCount(1, $variants);
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Variant', $variants[0]);

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
