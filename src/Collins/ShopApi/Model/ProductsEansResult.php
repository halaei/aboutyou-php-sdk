<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi\Factory\ModelFactoryInterface;
use Collins\ShopApi;
use Symfony\Component\EventDispatcher\GenericEvent;

class ProductsEansResult extends AbstractProductsResult
{
    /**
     * @param \stdClass $jsonObject
     * @param ModelFactoryInterface $factory
     *
     * @return static
     */
    public static function createFromJson(\stdClass $jsonObject, ModelFactoryInterface $factory)
    {
        $productsEansResult = new static();
        /**
         * @todo fire the event in the function, which calls this method, while preserving the correct event key (constructor of the abstract class...)
         */
        $event = new GenericEvent($productsEansResult, func_get_args());
        ShopApi::getEventDispatcher()->dispatch('collins.shop_api.products_eans_result.from_json.before', $event);

        $productsEansResult->pageHash = isset($jsonObject->pageHash) ? $jsonObject->pageHash : null;

        foreach ($jsonObject->eans as $jsonProduct) {
            $productsEansResult->products[] = $factory->createProduct($jsonProduct);
        }

        ShopApi::getEventDispatcher()->dispatch('collins.shop_api.products_eans_result.from_json.after', $event);

        return $productsEansResult;
    }
}