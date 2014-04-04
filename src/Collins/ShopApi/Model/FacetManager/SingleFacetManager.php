<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model\FacetManager;

class SingleFacetManager extends AbstractFacetManager
{
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
