<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class BreadcrumbTestAbstract extends AbstractShopApiTest
{
     /**
     *
     */
    public function testFetchParentCategories()
    {
        $this->markTestIncomplete();

        $categoryId = 123;

        $categories = $this->api->fetchParentCategories($categoryId);

        $this->assertTrue(is_array($categories));
        foreach ($categories as $category) {
            $this->checkCategory($category);
        }
    }

    /**
     *
     */
    private function checkCategory($category)
    {
//        $this->assertObjectHasAttribute('id', $category);
//        $this->assertObjectHasAttribute('name', $category);
//        $this->assertObjectHasAttribute('active', $category);
        $this->assertNotNull($category->id);
        $this->assertNotNull($category->name);
        $this->assertNotNull($category->active);
        //TODO: check if this is a category
    }
}
