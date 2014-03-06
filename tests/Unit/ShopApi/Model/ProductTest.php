<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Unit\Model;

use Collins\ShopApi\Model\Product;

class ProductTest extends AbstractModelTest
{
    public function testConstructor()
    {
        $json = json_decode('{"id":1,"name":"Product"}');
        new Product($json);
    }

    /**
     * @expectedException \Collins\ShopApi\Exception\MalformedJsonException
     */
    public function testMalformedJsonException()
    {
        $json = json_decode('{"id":1,"but":"now name attribute"}');
        new Product($json);
    }

    /**
     * @expectedException \Collins\ShopApi\Exception\RuntimeException
     */
    public function testGetBrandRuntimeException()
    {
        $json = json_decode('{"id":1,"name":"Product"}');
        $product = new Product($json);
        $product->getBrand();
    }
}
 