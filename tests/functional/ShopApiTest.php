<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;
use Guzzle\Http\Message\Response;
use Guzzle\Service\Client;

abstract class ShopApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Client
     */
    protected function getGuzzleClient($jsonString)
    {
        $response = new Response('200 OK', null, $jsonString);

        $request = $this->getMockBuilder('Guzzle\\Http\\Message\\EntityEnclosingRequest')
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->any())
            ->method('send')
            ->will($this->returnValue($response));

        $client = $this->getMock('Guzzle\\Http\\Client');
        $client->expects($this->any())
            ->method('post')
            ->will($this->returnValue($request));

        return $client;
    }

    /**
     * @param $jsonString
     *
     * @return ShopApi
     */
    protected function getShopApiWithResult($jsonString)
    {
        $client = $this->getGuzzleClient($jsonString);

        $shopApi = new ShopApi('id', 'token');
        $shopApi->setClient($client);

        return $shopApi;
    }
}
