<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Model\FacetManager;

interface FacetManagerInterface
{
    /**
     * @return boolean
     */
    public function isEmpty();

    /**
     * @param \AboutYou\SDK\Model\Facet[][] $facets
     */
    public function setFacets($facets);

    /**
     * @param int $groupId group id of a facet
     * @param int $id id of the facet
     *
     * @return \AboutYou\SDK\Model\Facet
     */
    public function getFacet($groupId, $id);

    /**
     * @param int[] $groups
     * @return array
     */
    public function getFacetsByGroups($groups);
}
