<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model\Basket;

class BasketItem extends BasketVariantItem implements BasketItemInterface
{
    /** @var string */
    protected $id;

    /**
     * Constructor.
     *
     * @param string $id
     * @param integer $variantId
     * @param array $additionalData
     */
    public function __construct($id, $variantId, array $additionalData = null)
    {
        $this->id = $id;
        parent::__construct($variantId, $additionalData);
    }

    /**
     * @param object $jsonObject The basket data.
     * @param Product[] $products
     *
     * @return BasketItem
     */
    public static function createFromJson($jsonObject, array $products)
    {
        $item = new self($jsonObject->id, $jsonObject->variant_id, isset($jsonObject->additional_data) ? $jsonObject->additional_data : null);
        $item->parseErrorResult($jsonObject);

        $item->jsonObject = $jsonObject;

        if ($products[$jsonObject->product_id]) {
            $item->setProduct($products[$jsonObject->product_id]);
        }
        unset($jsonObject->id, $jsonObject->variant_id, $jsonObject->additional_data, $jsonObject->product_id);

        return $item;
    }

    public function getId()
    {
        return $this->id;
    }
}