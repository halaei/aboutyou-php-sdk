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
    public function __construct(ShopApi $shopApi)
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
            $facetIds = array_values(array_unique($facetIds));

            foreach ($facetIds as $facetId) {
                $apiQueryParams[] = array('id' => $facetId, 'group_id' => $groupId);
            }
        }

        return $this->shopApi->fetchFacet($apiQueryParams);
    }
}