<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\Test\Functional;

use Collins\ShopApi;
use Guzzle\Http\Message\Response;
use Guzzle\Service\Client;


class ShopApiCategoryTreeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Collins\ShopApi
     */
    private $api = null;

    /**
     *
     */
    public function setUp()
    {
        $this->api = new ShopApi('106', '7898aaf62cccbeb7210660b86ac80847');

    }

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
     *
     */
    public function testFetchCategoryTree()
    {
        $depth = 1;

        $jsonString = file_get_contents(__DIR__.'/testData/app-category-tree.json');
        $client = $this->getGuzzleClient($jsonString);

        $this->api->setClient($client);

        $categories = $this->api->fetchCategoryTree($depth);

        foreach ($categories as $category) {
            $this->checkCategory($category);

            foreach ($category->getSubCategories() as $subCategory) {
                $this->checkCategory($subCategory);
                $this->assertEquals($category, $subCategory->getParent());
                $this->assertEmpty($subCategory->getSubCategories);
            }
        }
    }


    /**
     *
     */
    private function checkCategory($category)
    {
        $this->assertObjectHasAttribute('id', $category);
        $this->assertObjectHasAttribute('name', $category);
        $this->assertObjectHasAttribute('isActive', $category);
        $this->assertNotNull($category->id);
        $this->assertNotNull($category->name);
        $this->assertNotNull($category->isActive);
        //TODO: check if this is a category
    }
}
