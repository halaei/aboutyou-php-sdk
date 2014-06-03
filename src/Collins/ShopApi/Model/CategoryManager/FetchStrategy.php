<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\CategoryManager;

use Collins\ShopApi;

class FetchStrategy implements  FetchStrategyInterface
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

    public function fetch()
    {

    }
}