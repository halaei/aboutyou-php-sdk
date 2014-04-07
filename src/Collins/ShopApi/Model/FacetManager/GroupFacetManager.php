<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model\FacetManager;

class GroupFacetManager extends AbstractFacetManager
{
    protected function preFetch()
    {
        $this->facets += $this->shopApi->fetchFacets(array_keys($this->missingFacetGroupIdsAndFacetIds));
        $this->missingFacetGroupIdsAndFacetIds = array();
    }
}
