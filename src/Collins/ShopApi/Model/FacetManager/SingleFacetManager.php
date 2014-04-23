<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\FacetManager;

/**
 * @deprecated use DefaultFacetManager with FetchSingleFacetStrategy instead
 */
class SingleFacetManager extends AbstractFacetManager
{
    /** @var  \Collins\ShopApi */
    protected $shopApi;

    protected function preFetch()
    {
        if (empty($this->missingFacetGroupIdsAndFacetIds)) {
            return;
        }

        /** @var  $cachedFacets Facet[] */
        $apiQueryParams = array();

        foreach ($this->missingFacetGroupIdsAndFacetIds as $groupId => $facetIds) {
            unset($this->missingFacetGroupIdsAndFacetIds[$groupId]);

            $facetIds = array_values(array_unique($facetIds));

            foreach ($facetIds as $facetId) {
                $apiQueryParams[] = array('id' => $facetId, 'group_id' => $groupId);
            }
        }

        $this->facets += $this->shopApi->fetchFacet($apiQueryParams);
    }
}
