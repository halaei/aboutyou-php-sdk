<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FacetManager implements FacetManagerInterface, EventSubscriberInterface
{
    /** @var Facet[][] */
    private $facets;

    private $cache;

    public static function getSubscribedEvents()
    {
        return array(
            //'example.from_json' => array('onExampleFromJson', 0),
        );
    }

    public function parseJson(array $json)
    {
        foreach ($json as $singleFacet) {
            $this->facets[$singleFacet->id][$singleFacet->facet_id] = $singleFacet;
        }
    }

    public function preFetch($brandIds, $facetGroupIds)
    {

    }

    /**
     * @param $groupId
     * @param $id
     *
     * @return Facet
     */
    public function getFacet($groupId, $id)
    {
        $facet = $this->facets[$groupId][$id];
        if (!$facet instanceof Facet) {
            $this->facets[$groupId][$id] = $facet = Facet::createFromJson($facet);
        }

        return $facet;
    }
} 
