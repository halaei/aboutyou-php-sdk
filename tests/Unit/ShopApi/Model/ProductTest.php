<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Unit\Model;

use Collins\ShopApi\Factory\DefaultModelFactory;
use Collins\ShopApi\Model\Product;

class ProductTest extends AbstractModelTest
{
    public function testConstructor()
    {
        $json = json_decode('{"id":1,"name":"Product"}');
        new Product($json, $this->getModelFactory());
    }

    /**
     * @expectedException \Collins\ShopApi\Exception\MalformedJsonException
     */
    public function testMalformedJsonException()
    {
        $json = json_decode('{"id":1,"but":"now name attribute"}');
        new Product($json, $this->getModelFactory());
    }

    /**
     * @expectedException \Collins\ShopApi\Exception\RuntimeException
     */
    public function testGetBrandRuntimeException()
    {
        $json = json_decode('{"id":1,"name":"Product"}');
        $product = new Product($json, $this->getModelFactory());
        $product->getBrand();
    }
}
 