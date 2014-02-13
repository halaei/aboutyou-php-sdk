<?php
namespace Collins\ShopApi\Model;

/**
 *
 */
class Basket
{
    /**
     * @var object
     */
    protected $jsonObject = null;

    /**
     * @var BasketItem[]
     */
    private $items = array();

    /**
     * Constructor.
     *
     * @param object $jsonObject The basket data.
     */
    public function __construct($jsonObject)
    {
        $this->jsonObject = $jsonObject;
    }

    /**
     * Create basket item from json object.
     *
     * @param object $jsonItem
     *
     * @return BasketItem
     */
    protected function createBasketItem($jsonItem)
    {
        return new BasketItem($jsonItem);
    }

    /**
     * Get the total price.
     *
     * @return integer
     */
    public function getTotalPrice()
    {
        return $this->jsonObject->total_price;
    }

    /**
     * Get the total net.
     *
     * @return integer
     */
    public function getTotalNet()
    {
        return $this->jsonObject->total_net;
    }

    /**
     * Get the total vat.
     *
     * @return integer
     */
    public function getTotalVat()
    {
        return $this->jsonObject->total_vat;
    }

    /**
     * Get the total quantity of all items.
     *
     * @return integer
     */
    public function getTotalQuantity()
    {
        return $this->jsonObject->amount_variants;
    }

    /**
     * Get the number of variants.
     *
     * @return integer
     */
    public function getTotalVariants()
    {
        return $this->jsonObject->total_variants;
    }

    /**
     * Get all basket items.
     *
     * @return BasketItem[]
     */
    public function getItems()
    {
        if( !$this->items ) {
            foreach ($this->jsonObject->product_variant as $jsonItem) {
                if( isset($this->jsonObject->products->{$jsonItem->product_id}) ) {
                    $jsonItem->product = $this->jsonObject->products->{$jsonItem->product_id};
                    $this->items[] = $this->createBasketItem($jsonItem);
                }
            }
        }
        return $this->items;
    }
}