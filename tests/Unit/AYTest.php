<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Unit;

use AboutYou\SDK\Constants;
use AboutYou\SDK\Model\FacetManager\AboutyouCacheStrategy;
use AboutYou\SDK\Model\FacetManager\FetchSingleFacetStrategy;

class AYTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorWithDefaultStrategy()
    {
        $appId = '123';
        $appPassword = 'abc';
        $apiEndPoint = 'http://localhost.dev/api';
        $loggerInterfaceImplementation = $this->getMock('\\Psr\\Log\\LoggerInterface');
        $cacheMock = $this->getMockForAbstractClass('\\Aboutyou\\Common\\Cache\\CacheProvider');

        $ay = new \AY(
            $appId,
            $appPassword,
            $apiEndPoint,
            null,
            $loggerInterfaceImplementation,
            $cacheMock
        );

        $this->assertEquals($apiEndPoint, $ay->getApiEndPoint());
        $this->assertEquals($loggerInterfaceImplementation, $ay->getLogger());
        $factory = $ay->getResultFactory();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Factory\\DefaultModelFactory', $factory);
        $facetManager = $factory->getFacetManager();

        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\FacetManager\\DefaultFacetManager', $facetManager);

        /**
         * relies on internal configuration of live image url as constant
         */
        $this->assertEquals(\AY::IMAGE_URL_LIVE, $ay->getBaseImageUrl());
    }

    /**
     * Testing the constructor with setting the stage environment constant as api endpoint
     * and overwriting
     */
    public function testConstructStageEnvironmentImageUrl()
    {
        $appId = '123';
        $appPassword = 'abc';
        $apiEndPoint = Constants::API_ENVIRONMENT_STAGE;
        $apiEndPointLive = Constants::API_ENVIRONMENT_LIVE;

        $ay = new \AY(
            $appId,
            $appPassword,
            $apiEndPoint
        );

        /**
         * this assertion relies on internal implementation and relieng on live environment
         * urls as constant, point to refactor but actually needs a test
         */
        $this->assertEquals($ay::IMAGE_URL_STAGE, $ay->getBaseImageUrl());
        $this->assertEquals('//devcenter-staging-www1.pub.collins.kg/appjs/123.js', $ay->getJavaScriptURL());
        
        $ayLive = new \AY(
            $appId,
            $appPassword,
            $apiEndPointLive
        );   
        
        $this->assertEquals('//developer.aboutyou.de/appjs/123.js', $ayLive->getJavaScriptURL());
    }

    /**
     *
     * @return \AY
     */
    private function getTestObject()
    {
        $appId = '123';
        $appPassword = 'abc';
        $apiEndPoint = Constants::API_ENVIRONMENT_STAGE;

        $ay = new \AY(
            $appId,
            $appPassword,
            $apiEndPoint
        );

        return $ay;
    }

    /**
     * Test get query function and if it initialized correctly
     */
    public function testGetQuery()
    {
        $ay = $this->getTestObject();
        $query = $ay->getQuery();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Query', $query);
    }

    /**
     * Testing getter for shop api client and if it initialized correctly
     */
    public function testGetApiClient()
    {
        $ay     = $this->getTestObject();
        $client = $ay->getApiClient();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Client', $client);
    }

    public function testGetModelFactory()
    {
        $ay = $this->getTestObject();
        $modelFactory = $ay->getResultFactory();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Factory\\ResultFactoryInterface', $modelFactory);
    }

}
 