<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model\FacetManager;

use Collins\ShopApi;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface FacetManagerInterface extends EventSubscriberInterface
{
    /**
     * @param \Collins\ShopApi $shopApi
     */
    public function setShopApi(ShopApi $shopApi);

    /**
     * @param $groupId group id of a facet
     * @param $id id of the facet
     *
     * @return \Collins\ShopApi\Model\Facet
     */
    public function getFacet($groupId, $id);
} 