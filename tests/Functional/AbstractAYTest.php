<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Functional;

use AboutYou\SDK\Model\Facet;
use AboutYou\SDK\Model\FacetManager;
use Guzzle\Http\Message\Response;
use Guzzle\Service\Client;

abstract class AbstractAYTest extends \AboutYou\SDK\Test\AYTest
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

        $facetManager = new FacetManager\StaticFacetManager($facets);

        return $facetManager;
    }

    protected function getAYWithResultFileAndFacets($filepath, $facetsFile)
    {
        $jsonString = $this->getJsonStringFromFile($filepath);

        $ay = $this->getAYWithResult($jsonString);

        $facetManager = $this->getStaticFacetManagerFromFile($facetsFile);
        $ay->getResultFactory()->setFacetManager($facetManager);

        return $ay;
    }

    protected function getAYWithResultFile($filepath, $exceptedRequestBody = null)
    {
        $jsonString = $this->getJsonStringFromFile($filepath);

        return $this->getAYWithResult($jsonString, $exceptedRequestBody);
    }

    protected function getAYWithResultFiles($filepaths, $exceptedRequestBody = null)
    {
        $jsonStrings = array();
        foreach ($filepaths as $filepath) {
            $jsonStrings[] = $this->getJsonStringFromFile($filepath);
        }

        return $this->getAYWithResult($jsonStrings, $exceptedRequestBody);
    }

    /**
     * @param string|string[] $jsonString
     * @param string $exceptedRequestBody
     *
     * @return \AY
     */
    protected function getAYWithResult($jsonString, $exceptedRequestBody = null)
    {
        $client = $this->getGuzzleClient($jsonString, $exceptedRequestBody);

        $ay = new \AY('100', 'token');
        $ay->getApiClient()->setClient($client);
        if ($this->setupCategoryManager === true) {
            $this->setupCategoryManager($ay);
        }
        if ($this->facetsResultPath) {
            $facets = $this->getFacetList($this->facetsResultPath);
            $ay->getResultFactory()->getFacetManager()->setFacets($facets);
        }

        return $ay;
    }

    protected function getMockedAYWithResultFile(array $methods, $filepath, $exceptedRequestBody = null)
    {
        $jsonString = $this->getJsonStringFromFile($filepath);

        return $this->getMockedAYWithResult($methods, $jsonString, $exceptedRequestBody);
    }

    /**
     * @param array $methods
     * @param string|string[] $jsonString
     * @param string|null $exceptedRequestBody
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\AY
     */
    protected function getMockedAYWithResult(array $methods, $jsonString, $exceptedRequestBody = null)
    {
        $client = $this->getGuzzleClient($jsonString, $exceptedRequestBody);

        $ay = $this->getMock('AY', $methods, array('id', 'token'));

        $ay->getApiClient()->setClient($client);

        return $ay;
    }

    protected function setupCategoryManager($ay)
    {
        $categoryManager = $ay->getResultFactory()->getCategoryManager();
        $json = $this->getJsonObjectFromFile('category-tree-v2.json');
        $categoryManager->parseJson($json[0]->category_tree, $ay->getResultFactory());
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
            $facet = Facet::createFromJson($jsonFacet);
            $facets[$facet->getUniqueKey()] = $facet;
        }

        return $facets;
    }
}
