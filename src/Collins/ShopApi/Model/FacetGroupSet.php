<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi;

class FacetGroupSet extends AbstractModel implements FacetUniqueKeyInterface
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
        foreach ($ids as $facetIds) {
            if (!is_array($facetIds)) {
                throw new ShopApi\Exception\InvalidParameterException('$ids must be an associative array of array: [$groupId => [$facetId,...],...]');
            }
        }

        $this->ids = $ids;
    }

    /**
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->ids);
    }

    public function getIds()
    {
        return $this->ids;
    }

    public function getGroups()
    {
        if ($this->groups == null) {
            $this->fetch();
        }
        
        return $this->groups;
    }

    protected function fetch()
    {
        if ($this->facets !== null) {
            return;
        }

        $shopApi = $this->getShopApi();

        $groupIds = array_keys($this->ids);
        $allFacets = $shopApi->fetchFacets($groupIds);

        $this->facets = array();
        $this->groups = array();

        foreach ($this->ids as $groupId => $facetIds) {

            foreach ($facetIds as $facetId) {
                $key = Facet::uniqueKey($groupId, $facetId);
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
     * @param $groupId
     *
     * @return FacetGroup|null
     */
    public function getGroup($groupId)
    {
        $groups = $this->getGroups();
        if (isset($groups[$groupId])) {
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
     * @param FacetUniqueKeyInterface $facetCompable
     *
     * @return boolean
     */
    public function contains(FacetUniqueKeyInterface $facetCompable)
    {
        if ($facetCompable instanceof FacetGroupSet) {
            return $this->containsFacetGroupSet($facetCompable);
        }

        if ($facetCompable instanceof FacetGetGroupInterface) {
            return $this->containsFacetGetGroupInterface($facetCompable);
        }

        return false;
    }

    private function containsFacetGetGroupInterface(FacetGetGroupInterface $facet)
    {
        $myLazyGroups = $this->getLazyGroups();
        $id = $facet->getGroupId();

        if (isset($myLazyGroups[$id])) {
            return $myLazyGroups[$id]->getUniqueKey() === $facet->getUniqueKey();
        }

        return false;
    }

    private function containsFacetGroupSet(FacetGroupSet $facetGroupSet)
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

    /**
     * @return array
     */
    public function getGroupIds()
    {
        return array_keys($this->ids);
    }
}