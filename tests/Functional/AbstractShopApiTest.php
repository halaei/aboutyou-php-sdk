<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;
use Guzzle\Http\Message\Response;
use Guzzle\Service\Client;

abstract class AbstractShopApiTest extends \Collins\ShopApi\Test\ShopSdkTest
{
    protected $setupCategoryManager = true;

    protected $facetsResultPath = 'facets-for-product-variant-facets.json';

    /**
     * @param string|string[] $jsonString
     * @param string|null $exceptedRequestBody
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Client
     */
    protected function getGuzzleClient($jsonString, $exceptedRequestBody = null)
    {
        $request = $this->getMockBuilder('Guzzle\\Http\\Message\\EntityEnclosingRequest')
            ->disableOriginalConstructor()
            ->getMock();

        if (is_array($jsonString)) {
            $responses = array();
            foreach ($jsonString as $json) {
                $responses[] = new Response('200 OK', null, $json ?: '');
            }
            $request->expects($this->any())
                ->method('send')
                ->will(call_user_func_array(array($this, 'onConsecutiveCalls'), $responses));
        } else {
            $response = new Response('200 OK', null, $jsonString ?: '');
            $request->expects($this->any())
                ->method('send')
                ->will($this->returnValue($response));
        }

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

    protected function getJsonStringFromFile($filepath, $baseDir = __DIR__)
    {
        if (strpos($filepath, '/') !== 0) {
            $filepath = $baseDir . '/testData/' . $filepath;
        }
        $jsonString = file_get_contents($filepath);

        return $jsonString;
    }

    protected function getStaticFacetManagerFromFile($filename)
    {
        $facets = $this->getFacetList($filename);

        $facetManager = new ShopApi\Model\FacetManager\StaticFacetManager($facets);

        return $facetManager;
    }

    protected function getShopApiWithResultFileAndFacets($filepath, $facetsFile)
    {
        $jsonString = $this->getJsonStringFromFile($filepath);

        $shopApi = $this->getShopApiWithResult($jsonString);

        $facetManager = $this->getStaticFacetManagerFromFile($facetsFile);
        $shopApi->getResultFactory()->setFacetManager($facetManager);

        return $shopApi;
    }

    protected function getShopApiWithResultFile($filepath, $exceptedRequestBody = null)
    {
        $jsonString = $this->getJsonStringFromFile($filepath);

        return $this->getShopApiWithResult($jsonString, $exceptedRequestBody);
    }

    protected function getShopApiWithResultFiles($filepaths, $exceptedRequestBody = null)
    {
        $jsonStrings = array();
        foreach ($filepaths as $filepath) {
            $jsonStrings[] = $this->getJsonStringFromFile($filepath);
        }

        return $this->getShopApiWithResult($jsonStrings, $exceptedRequestBody);
    }

    /**
     * @param string|string[] $jsonString
     * @param string $exceptedRequestBody
     *
     * @return ShopApi
     */
    protected function getShopApiWithResult($jsonString, $exceptedRequestBody = null)
    {
        $client = $this->getGuzzleClient($jsonString, $exceptedRequestBody);

        $shopApi = new ShopApi('100', 'token');
        $shopApi->getApiClient()->setClient($client);
        if ($this->setupCategoryManager === true) {
            $this->setupCategoryManager($shopApi);
        }
        if ($this->facetsResultPath) {
            $facets = $this->getFacetList($this->facetsResultPath);
            $shopApi->getResultFactory()->getFacetManager()->setFacets($facets);
        }

        return $shopApi;
    }

    protected function getMockedShopApiWithResultFile(array $methods, $filepath, $exceptedRequestBody = null)
    {
        $jsonString = $this->getJsonStringFromFile($filepath);

        return $this->getMockedShopApiWithResult($methods, $jsonString, $exceptedRequestBody);
    }

    /**
     * @param array $methods
     * @param string|string[] $jsonString
     * @param string|null $exceptedRequestBody
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|ShopApi
     */
    protected function getMockedShopApiWithResult(array $methods, $jsonString, $exceptedRequestBody = null)
    {
        $client = $this->getGuzzleClient($jsonString, $exceptedRequestBody);

        $shopApi = $this->getMock('Collins\\ShopApi', $methods, array('id', 'token'));

        $shopApi->getApiClient()->setClient($client);

        return $shopApi;
    }

    protected function setupCategoryManager($shopApi)
    {
        $categoryManager = $shopApi->getResultFactory()->getCategoryManager();
        $json = $this->getJsonObjectFromFile('category-tree-v2.json');
        $categoryManager->parseJson($json[0]->category_tree, $shopApi->getResultFactory());
    }

    /**
     * @param $filename
     * @return array
     */
    protected function getFacetList($filename)
    {
        $jsonObject = $this->getJsonObjectFromFile($filename);
        if (isset($jsonObject[0]->facets->facet)) {
            $jsonFacets = $jsonObject[0]->facets->facet;
        } else {
            $jsonFacets = $jsonObject[0]->facet;
        }
        $facets = array();
        foreach ($jsonFacets as $jsonFacet) {
            $facet = ShopApi\Model\Facet::createFromJson($jsonFacet);
            $facets[$facet->getUniqueKey()] = $facet;
        }

        return $facets;
    }
}
