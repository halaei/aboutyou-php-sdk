<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model\Basket;


class BasketSetItem extends BasketVariantItem
{
    /**
     * @param object $jsonObject The basket data.
     * @param Product[] $products
     *
     * @return BasketVariantItem
     *
     * @throws \Collins\ShopApi\Exception\UnexpectedResultException
     */
    public static function createFromJson($jsonObject, array $products)
    {
        $item = new static($jsonObject->variant_id, isset($jsonObject->additional_data) ? (array)$jsonObject->additional_data : null);
        $item->parseErrorResult($jsonObject);

        $item->jsonObject = $jsonObject;
        
        if ($jsonObject->product_id !== null) {
            if (isset($products[$jsonObject->product_id])) {
                $item->setProduct($products[$jsonObject->product_id]);
            } else {
                throw new \Collins\ShopApi\Exception\UnexpectedResultException('Product with ID '.$jsonObject->product_id.' expected but wasnt received with the basket');
            }
        }        
        unset($jsonObject->variant_id, $jsonObject->additional_data, $jsonObject->product_id);

        return $item;
    }
} 