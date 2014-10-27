<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Functional;

use Aboutyou\Common\Cache\ArrayCache;
use AboutYou\SDK\Constants;
use AboutYou\SDK\Model\CategoryManager\DefaultCategoryManager;
use AboutYou\SDK\Model\FacetManager\DefaultFacetManager;

class PreCacheTest extends AbstractAYTest
{
    public function testPreCache()
    {
        $cache = new ArrayCache();
        $appId = '100';

        $ay = $this->getAY($appId, $cache);

        $ay->preCache(\AY::PRE_CACHE_ALL);


        $categoryManager = new DefaultCategoryManager($cache, $appId);

        $categories = $categoryManager->getAllCategories();
        $this->assertCount(1, $categories);
        $this->assertArrayHasKey(74415, $categories);


        $facetManager = new DefaultFacetManager($cache, $appId);

        $facet1     = $facetManager->getFacet(2, 126);
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Facet', $facet1);
        $this->assertEquals('10', $facet1->getValue());

        $facet2 = $facetManager->getFacet(173,2112);
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\Facet', $facet2);
        $this->assertEquals('S', $facet2->getName());

        $facets = $cache->fetch('AY:SDK:'.$appId.':facets');
        $this->assertCount(2, $facets);
    }

    /**
     * @param $appId
     * @param $cache
     * @return \AY
     */
    private function getAY($appId, $cache)
    {
        $jsonString = <<<EOL
[
    {"facets":{"facet":[
        {
            "name": "10",
            "facet_id": 126,
            "id": 2,
            "value": "10",
            "group_name": "size"
        },
        {
            "name": "S",
            "facet_id": 2112,
            "id": 173,
            "value": "S",
            "group_name": "clothing_unisex_int"
        }
    ]}},
    {"category_tree": {
        "parent_child": {
            "0": [
                "74415"
            ]
        },
        "ids": {
            "74415": {
                "active": true,
                "position": 1,
                "name": "Frauen",
                "parent": 0,
                "id": 74415
            }
        }
    }}
]
EOL;
        $exceptedRequestBody = '[{"facets":{}},{"category_tree":{"version":"2"}}]';


        $client = $this->getGuzzleClient($jsonString, $exceptedRequestBody);
        $ay = new \AY($appId, 'token', Constants::API_ENVIRONMENT_LIVE, null, null, $cache);
        $ay->getApiClient()->setClient($client);

        return $ay;
    }
}
 