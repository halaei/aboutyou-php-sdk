<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\FacetManager;

use Collins\ShopApi;
use Collins\ShopApi\Model\Facet;

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

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array();
    }

    public function setShopApi(ShopApi $shopApi)
    {
        // dummy
    }

    public function getFacet($groupId, $facetId)
    {
        $key = Facet::uniqueKey($groupId, $facetId);

        return isset($this->factes[$key]) ?
            $this->factes[$key] :
            null
        ;
    }
} 