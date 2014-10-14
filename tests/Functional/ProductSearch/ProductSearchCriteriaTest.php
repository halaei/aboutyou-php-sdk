<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Functional\ProductSearch;

use Collins\ShopApi;
use Collins\ShopApi\Criteria\ProductFields;
use Collins\ShopApi\Criteria\ProductSearchCriteria;

/**
 * Class SearchCriteriaTest
 * @package Collins\ShopApi\Test\Functional
 *
 * @see tests/unit/ShopApi/Criteria/ProductSearchCriteriaTestAbstract.php
 */
class ProductSearchCriteriaTest extends ShopApi\Test\Functional\AbstractShopApiTest
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
            ->selectProductFields(array(ProductFields::DEFAULT_IMAGE,  ProductFields::DEFAULT_VARIANT))
            ->sortBy(ProductSearchCriteria::SORT_TYPE_PRICE, ProductSearchCriteria::SORT_DESC)
            ->setLimit(40)
            ->selectCategories(true)
            ->filterByPriceRange(0,1000);

        $expected = '{"session_id":"my session","result":{"fields":["default_image","default_variant","attributes_merged"],"sort":{"by":"price","direction":"desc"},"limit":40,"offset":0,"categories":true},"filter":{"prices":{"to":1000}}}';
        $this->assertEquals($expected, json_encode($criteria->toArray()));
    }

    protected function getJsonStringFromFile($filepath)
    {
        if (strpos($filepath, '/') !== 0) {
            $filepath = __DIR__.'/testData/' . $filepath;
        }
        $jsonString = file_get_contents($filepath);

        return $jsonString;
    }
}
