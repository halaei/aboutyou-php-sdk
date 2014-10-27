<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Unit\Model;

abstract class AbstractModelTest extends \AboutYou\SDK\Test\AYTest
{
    protected function getJsonObject($filepath)
    {
        if (strpos($filepath, '/') !== 0) {
            $filepath = dirname(__DIR__) . '/testData/' . $filepath;
        }
        $jsonString = file_get_contents($filepath);

        return json_decode($jsonString);
    }

    /**
     * @return \AboutYou\SDK\Factory\ModelFactoryInterface
     */
    protected function getModelFactory()
    {
        $ay =  new \AY('id', 'token');

        return $ay->getResultFactory();
    }

    protected function getModelFactoryMock()
    {
        return $this->getMock('\\AboutYou\\Factory\\DefaultModelFactory', array(), array(), '', false);
    }
}
