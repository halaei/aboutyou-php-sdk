<?php
namespace AboutYou\SDK\Model;

use AboutYou\SDK\Factory\ModelFactoryInterface;
use stdClass;

/**
 * StylesResult
 *
 * @author Holger Reinhardt <holger.reinhardt@aboutyou.de>
 */
class StylesResult extends AbstractProductsResult
{
    /**
     * @var int[]
     */
    protected $idsNotFound = [];

    /**
     * @param stdClass              $jsonObject
     * @param ModelFactoryInterface $factory
     *
     * @return static
     */
    public static function createFromJson(stdClass $jsonObject, ModelFactoryInterface $factory)
    {
        $productsResult = new static();

        $productsResult->pageHash = isset($jsonObject->pageHash) ? $jsonObject->pageHash : null;

        if (isset($jsonObject->styles)) {
            foreach ($jsonObject->styles as $style) {
                foreach ($style as $jsonProduct) {
                    if (isset($jsonProduct->error_code)) {
                        $productsResult->idsNotFound[] = $jsonProduct->id;
                        $productsResult->errors[] = $jsonProduct;
                        continue;
                    }
                    $productsResult->products[$jsonProduct->id] = $factory->createProduct($jsonProduct);
                }
            }
        }

        return $productsResult;
    }

    /**
     * @return integer[] array of product ids
     */
    public function getProductsNotFound()
    {
        return $this->idsNotFound;
    }
}
