<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


class FacetManager implements FacetManagerInterface
{
    private $facets;

    public function parseJson(array $json)
    {
        foreach ($json as $singleFacet) {
            $this->facets[$singleFacet->id][$singleFacet->facet_id] = $singleFacet;
        }
    }

    public function preFetch()
    {

    }

    public function getFacet($groupId, $id)
    {

    }
} 