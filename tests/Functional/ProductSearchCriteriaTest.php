<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;
use Collins\ShopApi\Criteria\ProductFields;
use Collins\ShopApi\Criteria\ProductSearchCriteria;

/**
 * Class SearchCriteriaTest
 * @package Collins\ShopApi\Test\Functional
 *
 * @see tests/unit/ShopApi/Criteria/ProductSearchCriteriaTest.php
 */
class ProductSearchCriteriaTest extends ShopApiTest
{
    public function testGetSearchCriteria()
    {
        $shopApi = new ShopApi('id', 'token');

        $criteria = $shopApi->getProductSearchCriteria('my session');

        $this->assertInstanceOf('Collins\\ShopApi\\Criteria\\CriteriaInterface', $criteria);
        $this->assertInstanceOf('Collins\\ShopApi\\Criteria\\ProductSearchCriteria', $criteria);
        $this->assertEquals('{"session_id":"my session"}', json_encode($criteria->toArray()));

        $criteria->setLimit(10);
        $this->assertEquals('{"session_id":"my session","result":{"limit":10,"offset":0}}', json_encode($criteria->toArray()));

        $criteria = $shopApi->getProductSearchCriteria('my session')
            ->selectFields([ProductFields::DEFAULT_IMAGE,  ProductFields::DEFAULT_VARIANT])
            ->sortBy(ProductSearchCriteria::SORT_TYPE_PRICE, ProductSearchCriteria::SORT_DESC)
            ->setLimit(40)
            ->selectCategoryFacets(true)
            ->setPriceRange(0,1000);

        $expected = '{"session_id":"my session","result":{"fields":["default_image","default_variant"],"sort":{"by":"price","direction":"desc"},"limit":40,"offset":0,"categories":true},"filter":{"prices":{"to":1000}}}';
        $this->assertEquals($expected, json_encode($criteria->toArray()));
    }
}
