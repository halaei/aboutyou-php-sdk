<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\FacetManager;

class FetchFacetGroupStrategy implements FetchStrategyInterface
{
    /** @var ShopApi */
    protected $shopApi;

    /**
     * @param ShopApi $shopApi
     */
    public function __construct(ShopApi $shopApi)
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