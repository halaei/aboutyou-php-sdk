<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi;
use Collins\ShopApi\Model\CategoryManager\CategoryManagerInterface;

abstract class AbstractModel
{
    /** @var ShopApi */
    protected static $shopApi;

    /**
     * @param ShopApi $shopApi
     */
    public static function setShopApi(ShopApi $shopApi)
    {
        self::$shopApi = $shopApi;
    }

    /**
     * @return ShopApi
     */
    public function getShopApi()
    {
        return self::$shopApi;
    }

//    /**
//     * @return ShopApi\Factory\ModelFactoryInterface
//     */
//    public function getModelFactory()
//    {
//        $factory = $this->getShopApi()->getResultFactory();
//
//        return $factory;
//    }
}
