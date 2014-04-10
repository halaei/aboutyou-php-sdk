<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi\Factory\ModelFactoryInterface;
use Collins\ShopApi;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProductsResult extends AbstractProductsResult
{
    protected $productsNotFound = array();

    /**
     * @param \stdClass $jsonObject
     * @param ModelFactoryInterface $factory
     *
     * @return static
     */
    public static function createFromJson(\stdClass $jsonObject, ModelFactoryInterface $factory)
    {
        $productsResult = new static();
        /**
         * @todo fire the event in the function, which calls this method, while preserving the correct event key (constructor of the abstract class...)
         */
        $event = new GenericEvent($productsResult, func_get_args());
        ShopApi::getEventDispatcher()->dispatch('collins.shop_api.products_result.from_json.before', $event);
        $productsResult->pageHash = isset($jsonObject->pageHash) ? $jsonObject->pageHash : null;

        if (isset($jsonObject->ids)) {
            foreach ($jsonObject->ids as $key => $jsonProduct) {
                if (isset($jsonProduct->error_code)) {
                    $productsResult->productsNotFound[] = $key;
                    continue;
                }
                $productsResult->products[$key] = $factory->createProduct($jsonProduct);
            }
        }
        ShopApi::getEventDispatcher()->dispatch('collins.shop_api.products_result.from_json.after', $event);

        return $productsResult;
    }

    /**
     * @return array of product ids
     */
    public function getProductsNotFound()
    {
        return $this->productsNotFound;
    }
}