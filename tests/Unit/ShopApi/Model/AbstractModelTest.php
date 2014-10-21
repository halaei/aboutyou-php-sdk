<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace Collins\ShopApi\Test\Unit\Model;

use Collins\ShopApi;

abstract class AbstractModelTest extends \Collins\ShopApi\Test\ShopSdkTest
{
    protected function getJsonObject($filepath)
    {
        if (strpos($filepath, '/') !== 0) {
            $filepath = dirname(dirname(__DIR__)) . '/testData/' . $filepath;
        }
        $jsonString = file_get_contents($filepath);

        return json_decode($jsonString);
    }

    /**
     * @return ShopApi\Factory\ModelFactoryInterface
     */
    protected function getModelFactory()
    {
        $shopApi =  new ShopApi('id', 'token');

        return $shopApi->getResultFactory();
    }

    protected function getModelFactoryMock()
    {
        return $this->getMock('\\Collins\\ShopApi\\Factory\\DefaultModelFactory', array(), array(), '', false);
    }
}
