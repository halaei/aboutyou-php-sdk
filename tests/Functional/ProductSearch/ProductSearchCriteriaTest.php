<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Functional\ProductSearch;

use \AY;
use AboutYou\SDK\Criteria\ProductFields;
use AboutYou\SDK\Criteria\ProductSearchCriteria;
use AboutYou\SDK\Test\Functional\AbstractAYTest;

/**
 * Class SearchCriteriaTest
 * @package AboutYou\Test\Functional
 *
 * @see tests/unit/AboutYou/Criteria/ProductSearchCriteriaTestAbstract.php
 */
class ProductSearchCriteriaTest extends AbstractAYTest
{
    public function testGetSearchCriteria()
    {
        $ay = new AY('id', 'token');

        $criteria = $ay->getProductSearchCriteria('my session');

        $this->assertInstanceOf('\\AboutYou\\SDK\\Criteria\\CriteriaInterface', $criteria);
        $this->assertInstanceOf('\\AboutYou\\SDK\\Criteria\\ProductSearchCriteria', $criteria);
        $this->assertEquals('{"session_id":"my session"}', json_encode($criteria->toArray()));

        $criteria->setLimit(10);
        $this->assertEquals('{"session_id":"my session","result":{"limit":10,"offset":0}}', json_encode($criteria->toArray()));

        $criteria = $ay->getProductSearchCriteria('my session')
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
