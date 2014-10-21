<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Unit\Model;

use \AY;

abstract class AbstractModelTest extends \AboutYou\SDK\Test\ShopSdkTest
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
     * @return AboutYou\SDK\Factory\ModelFactoryInterface
     */
    protected function getModelFactory()
    {
        $shopApi =  new AY('id', 'token');

        return $shopApi->getResultFactory();
    }

    protected function getModelFactoryMock()
    {
        return $this->getMock('\\AboutYou\\Factory\\DefaultModelFactory', array(), array(), '', false);
    }
}
