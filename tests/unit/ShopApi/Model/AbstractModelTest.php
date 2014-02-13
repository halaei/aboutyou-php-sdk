<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Unit\ShopApi\Model;

abstract class AbstractModelTest extends \PHPUnit_Framework_TestCase
{
    protected function getJsonObject($filepath)
    {
        if (strpos($filepath, '/') !== 0) {
            $filepath = dirname(dirname(__DIR__)) . '/testData/' . $filepath;
        }
        $jsonString = file_get_contents($filepath);

        return json_decode($jsonString);
    }
} 