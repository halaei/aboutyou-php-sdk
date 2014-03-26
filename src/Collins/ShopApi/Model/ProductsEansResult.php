<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi\Factory\ModelFactoryInterface;

class ProductsEansResult extends AbstractProductsResult
{
    public function fromJson(\stdClass $jsonObject, ModelFactoryInterface $factory)
    {
        $this->pageHash = isset($jsonObject->pageHash) ? $jsonObject->pageHash : null;

        foreach ($jsonObject->eans as $jsonProduct) {
            $this->products[] = $factory->createProduct($jsonProduct);
        }
    }
}