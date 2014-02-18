<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi;
use Collins\ShopApi\Model\FacetGroup;

class FacetGroupSet
{
    /** @var array */
    protected $ids;

    /** @var FacetGroup[] */
    protected $groups;

    /** @var Facet[] */
    protected $facets;

    /**
     * @param array $ids two dimensional array of group ids and array ids
     */
    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    protected function genLazyGroups()
    {
        $groups = [];
        foreach ($this->ids as $groupId => $facetIds) {
            $group = new FacetGroup($groupId, null);
            foreach ($facetIds as $facetId) {
                $facet = new Facet($facetId, null, null, $groupId, null);
                $group->addFacet($facet);
            }
            $groups[$groupId] = $group;
        }

        return $groups;
    }

    public function getLazyGroups()
    {
        if ($this->facets !== null) {
            return $this->groups;
        } else {
            return $this->genLazyGroups();
        }
    }

    protected function fetch()
    {
        if ($this->facets !== null) return;

        // TODO: Refactore me
        $shopApi = ShopApi::getCurrentApi();

        $groupIds = array_keys($this->ids);
        $allFacets = $shopApi->fetchFacets($groupIds);

        $this->facets = [];
        $this->groups = [];

        foreach ($this->ids as $groupId => $facetIds) {
            foreach ($facetIds as $facetId) {
                $key = $groupId . '_' . $facetId;
                if (!isset($allFacets[$key])) {
                    // TODO: error handling
                    continue;
                }

                $facet = $allFacets[$key];

                if (isset($this->groups[$groupId])) {
                    $group = $this->groups[$groupId];
                } else {
                    $group = new FacetGroup($facet->getGroupId(), $facet->getGroupName());
                    $this->groups[$groupId] = $group;
                }

                $group->addFacet($facet);
                $this->facets[$facet->getUniqueKey()] = $facet;
            }
        }
    }

    /**
     * @return FacetGroup[]
     */
    public function getGroups()
    {
        $this->fetch();

        if ($this->groups === null) {
            $this->fetch();
        }

        return $this->groups;
    }

    /**
     * @return FacetGroup
     */
    public function getGroup($groupId)
    {
        $groups = $this->getGroups();
        if( isset($groups[$groupId]) ) {
            return $groups[$groupId];
        }

        return null;
    }

    /**
     * @param string $key
     *
     * @return Facet|null
     */
    public function getFacetByKey($key)
    {
        $this->fetch();

        return
            isset($this->facets[$key]) ?
            $this->facets[$key] :
            null
        ;
    }

    /**
     * set are equal, if all groups are equal
     * which means, that all included facet ids per group must be the same
     *
     * @return string
     */
    public function getUniqueKey()
    {
        $ids = $this->ids;
        asort($ids);
        array_walk($ids, 'sort');

        return json_encode($ids);
    }

    /**
     * @param FacetGroupSet $facetGroupSet
     *
     * @return boolean
     */
    public function contains(FacetGroupSet $facetGroupSet)
    {
        if ($this->getUniqueKey() === $facetGroupSet->getUniqueKey()) {
            return true;
        }

        $myLazyGroups = $this->getLazyGroups();
        foreach ($facetGroupSet->getLazyGroups() as $id => $group) {
            if (
                !isset($myLazyGroups[$id]) ||
                $myLazyGroups[$id]->getUniqueKey() !== $group->getUniqueKey()
            ) {
                return false;
            }
        }

        return true;
    }
}