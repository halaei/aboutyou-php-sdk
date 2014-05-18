<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\FacetManager;

use Collins\ShopApi;

class FetchSingleFacetStrategy implements FetchStrategyInterface
{
    /** @var ShopApi */
    protected $shopApi;

    /**
     * @param ShopApi $shopApi
     */
    public function __construct(ShopApi $shopApi=null)
    {
        $this->shopApi = $shopApi;
    }

    /**
     * @param ShopApi $shopApi
     */
    public function setShopApi(ShopApi $shopApi)
    {
        $this->shopApi = $shopApi;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($ids)
    {
        if (empty($ids)) {
            return array();
        }

        $apiQueryParams = array();

        foreach ($ids as $groupId => $facetIds) {
            foreach ($facetIds as $facetId) {
                $apiQueryParams[] = array('id' => (int)$facetId, 'group_id' => $groupId);
            }
        }

        if(isset($apiQueryParams[200])) { # We can request max 200 single items at once
            return $this->shopApi->fetchFacets(array_keys($ids));
        } else {
            return $this->shopApi->fetchFacet($apiQueryParams);
        }
    }
}