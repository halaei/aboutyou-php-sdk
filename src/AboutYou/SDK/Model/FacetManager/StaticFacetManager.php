<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Model\FacetManager;

use \AY;
use AboutYou\SDK\Model\Facet;

class StaticFacetManager implements FacetManagerInterface
{
    /** @var Facet[] */
    protected $factes;

    /**
     * @param Facet[] $facets
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $facets)
    {
        foreach ($facets as $facet) {
            if (! $facet instanceof Facet) {
                throw new \InvalidArgumentException('all facets must be an instance of Facet');
            }
            $this->factes[$facet->getUniqueKey()] = $facet;
        }
    }

    public function setFacets($facets)
    {

    }

    public function isEmpty()
    {
        return $this->factes === null;
    }

    public function getFacet($groupId, $facetId)
    {
        $key = Facet::uniqueKey($groupId, $facetId);

        return isset($this->factes[$key]) ?
            $this->factes[$key] :
            null
        ;
    }

    /**
     * @param int[] $groups
     * @return array
     */
    public function getFacetGroups($groups)
    {
        return [];
    }
}