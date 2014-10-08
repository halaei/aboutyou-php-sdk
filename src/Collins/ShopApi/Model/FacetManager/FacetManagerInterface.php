<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\FacetManager;

interface FacetManagerInterface
{
    /**
     * @return boolean
     */
    public function isEmpty();

    /**
     * @param \Collins\ShopApi\Model\Facet[][] $facets
     */
    public function setFacets($facets);

    /**
     * @param $groupId group id of a facet
     * @param $id id of the facet
     *
     * @return \Collins\ShopApi\Model\Facet
     */
    public function getFacet($groupId, $id);
} 