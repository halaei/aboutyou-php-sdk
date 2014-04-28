<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi;
use Collins\ShopApi\Model\FacetManager\FacetManagerInterface;

class FacetGroupSet extends AbstractModel implements FacetUniqueKeyInterface
{
    /** @var array */
    protected $ids;

    /** @var FacetGroup[] */
    protected $groups;

    /** @var Facet[] */
    protected $facets;

    /** @var FacetManagerInterface */
    protected static $facetManager;

    /**
     * @param array $ids two dimensional array of group ids and array ids
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $ids)
    {
        foreach ($ids as $facetIds) {
            if (!is_array($facetIds)) {
                throw new \InvalidArgumentException('$ids must be an associative array of array: [$groupId => [$facetId,...],...]');
            }
        }

        $this->ids = $ids;
    }

    public static function setFacetManager(FacetManagerInterface $facetManager)
    {
        self::$facetManager = $facetManager;
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

    protected function genLazyGroups()
    {
        $groups = array();
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
        if (!empty($this->facets)) return;

        foreach ($this->ids as $groupId => $facetIds) {

            foreach ($facetIds as $facetId) {
                $facet = self::$facetManager->getFacet($groupId, $facetId);

                if (empty($facet)) {
                    // TODO: error handling
                    continue;
                }

                if (isset($this->groups[$groupId])) {
                    $group = $this->groups[$groupId];
                } else {
                    $group = new FacetGroup($groupId, $facet->getGroupName());
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
        if (empty($this->groups)) {
            $this->fetch();
        }

        return($this->groups);
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

    public function getFacet($facetGroupId, $facetId)
    {
        if(empty($this->facets)) {
            $this->fetch();
        }

        if(isset($this->facets["$facetGroupId:$facetId"])) {
            return($this->facets["$facetGroupId:$facetId"]);
        }
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

    public static function mergeFacetIds($facetIdsArray)
    {
        $ids = array();
        foreach ($facetIdsArray as $facetIds) {
            foreach ($facetIds as $groupId => $facetIds) {
                if (isset( $ids[$groupId])) {
                    $ids[$groupId] = array_merge($ids[$groupId], $facetIds);
                } else {
                    $ids[$groupId] = $facetIds;
                }
            }
        }

        return $ids;
    }

    /**
     * @return array
     */
    public function getGroupIds()
    {
        return array_keys($this->ids);
    }
}