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
    public function fromJson(\stdClass $jsonObject, ModelFactoryInterface $factory)
    {
        /**
         * @todo fire the event in the function, which calls this method, while preserving the correct event key (constructor of the abstract class...)
         */
        $event = new GenericEvent($this, func_get_args());
        ShopApi::getEventDispatcher()->dispatch("collins.shop_api.products_eans_result.from_json.before", $event);

        $this->pageHash = isset($jsonObject->pageHash) ? $jsonObject->pageHash : null;

        foreach ($jsonObject->eans as $jsonProduct) {
            $this->products[] = $factory->createProduct($jsonProduct);
        }

        ShopApi::getEventDispatcher()->dispatch("collins.shop_api.products_eans_result.from_json.after", $event);
    }
}