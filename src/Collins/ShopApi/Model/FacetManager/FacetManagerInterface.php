<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\FacetManager;

use Collins\ShopApi;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface FacetManagerInterface extends EventSubscriberInterface
{
    /**
     * @param \Collins\ShopApi $shopApi
     * @deprecated the FetchStrategy implemention may need this information
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