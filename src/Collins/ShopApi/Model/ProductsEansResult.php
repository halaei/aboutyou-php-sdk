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

        $productsEansResult->pageHash = isset($jsonObject->pageHash) ? $jsonObject->pageHash : null;

        foreach ($jsonObject->eans as $jsonProduct) {
            $productsEansResult->products[] = $factory->createProduct($jsonProduct);
        }

        return $productsEansResult;
    }
}