<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


class ProductsEansResult extends AbstractProductsResult
{
    public function fromJson(\stdClass $jsonObject)
    {
        $this->pageHash = isset($jsonObject->pageHash) ? $jsonObject->pageHash : null;

        $factory = $this->getModelFactory();

        foreach ($jsonObject->eans as $jsonProduct) {
            $this->products[] = $factory->createProduct($jsonProduct);
        }
    }
}