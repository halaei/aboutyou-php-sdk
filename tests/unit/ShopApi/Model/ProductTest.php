<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Unit\ShopApi\Model;

use Collins\ShopApi\Model\Product;

class ProductTest extends AbstractModelTest
{
    /**
     * @expectedException \Collins\ShopApi\Exception\MalformedJsonException
     */
    public function testMalformedJsonException()
    {
        $json = json_decode('{"id":1,"but":"now name attribute"}');
        new Product($json);
    }
}
 