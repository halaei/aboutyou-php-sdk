<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi;
use Collins\ShopApi\Factory\ModelFactoryInterface;

class ProductsEansResult extends AbstractProductsResult
{
    /** @var string[] */
    protected $eansNotFound = array();

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

        if (isset($jsonObject->eans)) {
            foreach ($jsonObject->eans as $jsonProduct) {
                if (isset($jsonProduct->error_code)) {
                    $productsEansResult->errors[] = $jsonProduct;
                    $productsEansResult->eansNotFound = array_merge($productsEansResult->eansNotFound, $jsonProduct->ean);
                    continue;
                }
                $productsEansResult->products[] = $factory->createProduct($jsonProduct);
            }
        }

        return $productsEansResult;
    }

    public function getEansNotFound()
    {
        return $this->eansNotFound;
    }
}