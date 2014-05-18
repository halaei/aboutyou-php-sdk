<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\FacetManager;

use Collins\ShopApi;

class FetchFacetGroupStrategy implements FetchStrategyInterface
{
    /** @var ShopApi */
    protected $shopApi;

    /**
     * @param ShopApi $shopApi
     */
    public function __construct(ShopApi $shopApi=null)
    {
        if(!empty($shopApi)) {
            $this->setShopApi($shopApi);
        }
    }

    /**
     * @param ShopApi $shopApi
     */
    public function setShopApi(ShopApi $shopApi)
    {
        $this->shopApi = $shopApi;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($ids)
    {
        return $this->shopApi->fetchFacets(array_keys($ids));
    }
} 