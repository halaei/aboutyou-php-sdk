<?php
/**
 * @author nils.droege@project-collins.com
 * (c) ABOUT YOU GmbH
 */

namespace Collins\ShopApi\Test\Unit\Model;

use Collins\ShopApi\Factory\DefaultModelFactory;
use Collins\ShopApi\Model\Product;

class ProductTest extends AbstractModelTest
{
    public function testConstructor()
    {
        $json = json_decode('{"id":1,"name":"Product"}');
        Product::createFromJson($json, $this->getModelFactory(), 1);
    }

    /**
     * @expectedException \Collins\ShopApi\Exception\MalformedJsonException
     */
    public function testMalformedJsonException()
    {
        $json = json_decode('{"id":1,"but":"now name attribute"}');
        Product::createFromJson($json, $this->getModelFactory(), 1);
    }

    /**
     * @expectedException \Collins\ShopApi\Exception\RuntimeException
     */
    public function testGetBrandRuntimeException()
    {
        $json = json_decode('{"id":1,"name":"Product"}');
        $product = Product::createFromJson($json, $this->getModelFactory(), 1);
        $product->getBrand();
    }
}
 