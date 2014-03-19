<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;
use Guzzle\Http\Message\Response;
use Guzzle\Service\Client;

abstract class AbstractShopApiTest extends \Collins\ShopApi\Test\ShopSdkTest
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Client
     */
    protected function getGuzzleClient($jsonString, $exceptedRequestBody = null)
    {
        $request = $this->getMockBuilder('Guzzle\\Http\\Message\\EntityEnclosingRequest')
            ->disableOriginalConstructor()
            ->getMock();

        $response = new Response('200 OK', null, $jsonString ?: '');
        $request->expects($this->any())
            ->method('send')
            ->will($this->returnValue($response));

        if ($exceptedRequestBody) {
            $request->expects($this->any())
                ->method('setBody')
                ->with($exceptedRequestBody);
        }

        $client = $this->getMock('Guzzle\\Http\\Client');
        $client->expects($this->any())
            ->method('post')
            ->will($this->returnValue($request));

        return $client;
    }

    protected function getJsonObjectFromFile($filepath)
    {
        return json_decode($this->getJsonStringFromFile($filepath));
    }

    protected function getJsonStringFromFile($filepath)
    {
        if (strpos($filepath, '/') !== 0) {
            $filepath = __DIR__.'/testData/' . $filepath;
        }
        $jsonString = file_get_contents($filepath);

        return $jsonString;
    }

    protected function getShopApiWithResultFile($filepath, $exceptedRequestBody = null)
    {
        $jsonString = $this->getJsonStringFromFile($filepath);

        return $this->getShopApiWithResult($jsonString, $exceptedRequestBody);
    }


    /**
     * @param $jsonString
     *
     * @return ShopApi
     */
    protected function getShopApiWithResult($jsonString, $exceptedRequestBody = null)
    {
        $client = $this->getGuzzleClient($jsonString, $exceptedRequestBody);

        $shopApi = new ShopApi('id', 'token');

        $shopApi->getApiClient()->setClient($client);

        return $shopApi;
    }
}
