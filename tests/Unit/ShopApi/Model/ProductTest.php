<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Unit\Model;

use AboutYou\SDK\Factory\DefaultModelFactory;
use AboutYou\SDK\Model\Product;

class ProductTest extends AbstractModelTest
{
    public function testConstructor()
    {
        $json = json_decode('{"id":1,"name":"Product"}');
        Product::createFromJson($json, $this->getModelFactory(), 1);
    }

    /**
     * @expectedException \AboutYou\SDK\Exception\MalformedJsonException
     */
    public function testMalformedJsonException()
    {
        $json = json_decode('{"id":1,"but":"now name attribute"}');
        Product::createFromJson($json, $this->getModelFactory(), 1);
    }

    /**
     * @expectedException \AboutYou\SDK\Exception\RuntimeException
     */
    public function testGetBrandRuntimeException()
    {
        $json = json_decode('{"id":1,"name":"Product"}');
        $product = Product::createFromJson($json, $this->getModelFactory(), 1);
        $product->getBrand();
    }
}
 